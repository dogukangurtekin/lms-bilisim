<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentActivityLog;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LiveTrackingController extends Controller
{
    private const DISPLAY_TIMEZONE = 'Europe/Istanbul';

    private function activitySince(): Carbon
    {
        return now(self::DISPLAY_TIMEZONE)->subDays(10)->utc();
    }

    /**
     * Her öğrenci için tek seferde (öğrenci başına sorgu atmadan) log sayısı ve son hareket:
     * [student_id => ['count' => int, 'last' => ?StudentActivityLog]].
     */
    private function activitySummaries($studentIds, Carbon $since): array
    {
        $ids = collect($studentIds)->values();
        if ($ids->isEmpty()) {
            return [];
        }

        $stats = StudentActivityLog::query()
            ->whereIn('student_id', $ids)
            ->where('logged_at', '>=', $since)
            ->selectRaw('student_id, COUNT(*) AS c, MAX(id) AS last_id')
            ->groupBy('student_id')
            ->get();
        $lastLogs = StudentActivityLog::query()->whereIn('id', $stats->pluck('last_id'))->get()->keyBy('student_id');

        $out = [];
        foreach ($stats as $row) {
            $out[(int) $row->student_id] = ['count' => (int) $row->c, 'last' => $lastLogs->get($row->student_id)];
        }

        return $out;
    }

    /**
     * Öğretmense sadece kendi sınıflarını, adminde tümünü döner.
     */
    private function allowedClassIds(): ?array
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            return null; // null = kısıtlama yok
        }

        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) {
            return []; // öğretmen kaydı yoksa hiçbir şey göremesin
        }

        return SchoolClass::where('teacher_id', $teacher->id)->pluck('id')->toArray();
    }

    /**
     * Canlı Takip ana sayfası
     */
    public function index(Request $request)
    {
        $since          = $this->activitySince();
        $allowedClassIds = $this->allowedClassIds();
        $classId        = $request->input('class_id');
        $search         = trim($request->input('search', ''));

        // Sınıf listesi — öğretmene sadece kendi sınıfları
        $classesQuery = SchoolClass::orderBy('name');
        if ($allowedClassIds !== null) {
            $classesQuery->whereIn('id', $allowedClassIds);
        }
        $classes = $classesQuery->get();

        // Eğer seçili sınıf öğretmenin sınıfları arasında değilse sıfırla
        if ($classId && $allowedClassIds !== null && !in_array((int)$classId, $allowedClassIds)) {
            $classId = null;
        }

        $query = Student::with(['user', 'schoolClass'])
            ->whereHas('activityLogs', fn($q) => $q->where('logged_at', '>=', $since));

        // Öğretmen kısıtı — sadece kendi sınıflarındaki öğrenciler
        if ($allowedClassIds !== null) {
            $query->whereIn('school_class_id', $allowedClassIds ?: [0]);
        }

        if ($classId) {
            $query->where('school_class_id', $classId);
        }

        if ($search !== '') {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        // Tüm öğrencileri çek, son aktiviteye göre sırala, sonra paginate
        $found = $query->get();
        $summaries = $this->activitySummaries($found->pluck('id'), $since);
        $allStudents = $found
            ->map(function (Student $student) use ($summaries) {
                $last = $summaries[$student->id]['last'] ?? null;

                return [
                    'student'     => $student,
                    'log_count'   => $summaries[$student->id]['count'] ?? 0,
                    'last_seen'   => $last?->logged_at,
                    'last_action' => $last?->action_label,
                    'last_seen_time' => $last?->logged_at,
                ];
            })
            ->sortByDesc('last_seen')
            ->values();

        // Manuel paginate
        $perPage     = 20;
        $currentPage = (int) $request->input('page', 1);
        $total       = $allStudents->count();
        $students    = new \Illuminate\Pagination\LengthAwarePaginator(
            $allStudents->forPage($currentPage, $perPage),
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->except('page')]
        );

        $isTeacher = auth()->user()->hasRole('teacher');

        return view('live-tracking.index', compact(
            'students', 'since', 'classes', 'classId', 'search', 'isTeacher'
        ));
    }

    /**
     * Öğrenci detay sayfası — erişim kontrolü dahil
     */
    public function show(Student $student, Request $request)
    {
        $allowedClassIds = $this->allowedClassIds();

        // Öğretmen bu öğrencinin sınıfına erişemiyor
        if ($allowedClassIds !== null && !in_array($student->school_class_id, $allowedClassIds)) {
            abort(403, 'Bu öğrenciye erişim yetkiniz yok.');
        }

        $since = $this->activitySince();

        // Filtreler
        $dateFrom   = $request->input('date_from');   // YYYY-MM-DD
        $dateTo     = $request->input('date_to');     // YYYY-MM-DD
        $actionSearch = trim($request->input('action_search', ''));

        $logsQuery = StudentActivityLog::where('student_id', $student->id)
            ->where('logged_at', '>=', $since)
            ->orderByDesc('logged_at');

        if ($dateFrom) {
            $logsQuery->where('logged_at', '>=', Carbon::parse($dateFrom, self::DISPLAY_TIMEZONE)->startOfDay()->utc());
        }
        if ($dateTo) {
            $logsQuery->where('logged_at', '<=', Carbon::parse($dateTo, self::DISPLAY_TIMEZONE)->endOfDay()->utc());
        }
        if ($actionSearch !== '') {
            $logsQuery->where('action_label', 'like', "%{$actionSearch}%");
        }

        $logs      = $logsQuery->paginate(20)->withQueryString();
        $totalLogs = $logs->total();

        $student->load(['user', 'schoolClass']);

        return view('live-tracking.show', compact(
            'student', 'logs', 'since', 'totalLogs',
            'dateFrom', 'dateTo', 'actionSearch'
        ));
    }

    /**
     * AJAX — canlı güncelleme (filtreler + yetki dahil)
     */
    public function refresh(Request $request)
    {
        $since           = $this->activitySince();
        $allowedClassIds = $this->allowedClassIds();
        $classId         = $request->input('class_id');
        $search          = trim($request->input('search', ''));

        // Seçili sınıf yetkisiz ise yoksay
        if ($classId && $allowedClassIds !== null && !in_array((int)$classId, $allowedClassIds)) {
            $classId = null;
        }

        $query = Student::with(['user', 'schoolClass'])
            ->whereHas('activityLogs', fn($q) => $q->where('logged_at', '>=', $since));

        if ($allowedClassIds !== null) {
            $query->whereIn('school_class_id', $allowedClassIds ?: [0]);
        }

        if ($classId) {
            $query->where('school_class_id', $classId);
        }

        if ($search !== '') {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        // Aynı filtreyle birden çok öğretmen/sekme yenilese de sorgu 15 sn'de bir çalışır.
        $cacheKey = 'live_tracking:'.md5(json_encode([$allowedClassIds, $classId, $search]));
        $rows = Cache::remember($cacheKey, 15, function () use ($query, $since) {
        $found = $query->get();
        $summaries = $this->activitySummaries($found->pluck('id'), $since);

        return $found
            ->map(function (Student $student) use ($summaries) {
                $last = $summaries[$student->id]['last'] ?? null;
                return [
                    'id'          => $student->id,
                    'name'        => $student->user->name ?? '-',
                    'class'       => ($student->schoolClass->name ?? '') . ($student->schoolClass->section ? '-'.$student->schoolClass->section : '') ?: '-',
                    'last_seen'   => $last?->logged_at?->diffForHumans() ?? '-',
                    'last_seen_at' => $last?->logged_at?->getTimestamp() ?? 0,
                    'last_action' => $last?->action_label ?? '-',
                    'last_seen_time' => $last?->logged_at?->copy()->setTimezone(self::DISPLAY_TIMEZONE)->format('H:i') ?? '-',
                    'log_count'   => $summaries[$student->id]['count'] ?? 0,
                    'detail_url'  => route('live-tracking.show', $student),
                ];
            })
            ->sortByDesc('last_seen_at')
            ->values()
            ->all();
        });

        return response()->json($rows);
    }
}
