<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentActivityLog;
use App\Models\Teacher;
use Illuminate\Http\Request;

class LiveTrackingController extends Controller
{
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
        $since          = now()->subDays(10);
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
        $allStudents = $query->get()
            ->map(function (Student $student) use ($since) {
                $logs = StudentActivityLog::where('student_id', $student->id)
                    ->where('logged_at', '>=', $since)
                    ->orderByDesc('logged_at')
                    ->get();

                return [
                    'student'     => $student,
                    'log_count'   => $logs->count(),
                    'last_seen'   => $logs->first()?->logged_at,
                    'last_action' => $logs->first()?->action_label,
                    'first_seen'  => $logs->last()?->logged_at,
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

        $since = now()->subDays(10);

        // Filtreler
        $dateFrom   = $request->input('date_from');   // YYYY-MM-DD
        $dateTo     = $request->input('date_to');     // YYYY-MM-DD
        $actionSearch = trim($request->input('action_search', ''));

        $logsQuery = StudentActivityLog::where('student_id', $student->id)
            ->where('logged_at', '>=', $since)
            ->orderByDesc('logged_at');

        if ($dateFrom) {
            $logsQuery->where('logged_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $logsQuery->where('logged_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
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
        $since           = now()->subDays(10);
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

        $rows = $query->get()
            ->map(function (Student $student) use ($since) {
                $last = StudentActivityLog::where('student_id', $student->id)
                    ->where('logged_at', '>=', $since)
                    ->orderByDesc('logged_at')
                    ->first();

                return [
                    'id'          => $student->id,
                    'name'        => $student->user->name ?? '-',
                    'class'       => $student->schoolClass->name ?? '-',
                    'last_seen'   => $last?->logged_at?->diffForHumans() ?? '-',
                    'last_action' => $last?->action_label ?? '-',
                    'detail_url'  => route('live-tracking.show', $student),
                ];
            })
            ->sortByDesc(fn($r) => $r['last_seen'])
            ->values();

        return response()->json($rows);
    }
}
