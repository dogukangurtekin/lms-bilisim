<?php

namespace App\Http\Controllers;

use App\Models\CompetitionParticipant;
use App\Models\ContentProgress;
use App\Models\Course;
use App\Models\Grade;
use App\Models\LiveQuizAnswer;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentGameAssignmentProgress;
use App\Models\StudentHomeworkProgress;
use App\Models\StudentTimeStat;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    private const DEFAULT_WIDGETS = [
        'summary' => ['visible' => true, 'span' => 12, 'order' => 10, 'title' => 'Özet', 'type' => 'summary'],
        'attendance' => ['visible' => true, 'span' => 4, 'order' => 20, 'title' => 'Katılım', 'type' => 'stat'],
        'progress' => ['visible' => true, 'span' => 4, 'order' => 30, 'title' => 'İlerleme', 'type' => 'stat'],
        'students' => ['visible' => true, 'span' => 4, 'order' => 40, 'title' => 'Öğrenci Sayısı', 'type' => 'stat'],
        'classes' => ['visible' => true, 'span' => 4, 'order' => 50, 'title' => 'Sınıf Sayısı', 'type' => 'stat'],
        'courses' => ['visible' => true, 'span' => 4, 'order' => 60, 'title' => 'Ders Sayısı', 'type' => 'stat'],
        'xp' => ['visible' => true, 'span' => 4, 'order' => 70, 'title' => 'Toplam XP', 'type' => 'stat'],
        'chart_success_distribution' => ['visible' => true, 'span' => 4, 'order' => 85, 'title' => 'Başarı Dağılımı', 'type' => 'chart'],
        'chart_student_lesson_completion' => ['visible' => true, 'span' => 12, 'order' => 95, 'title' => 'Öğrenci Ders Tamamlama', 'type' => 'chart'],
        'signals' => ['visible' => true, 'span' => 6, 'order' => 80, 'title' => 'Sınıf Sinyalleri', 'type' => 'signals'],
        'notes' => ['visible' => true, 'span' => 6, 'order' => 90, 'title' => 'Öğretmen Notları', 'type' => 'notes'],
        'leaderboard' => ['visible' => true, 'span' => 12, 'order' => 100, 'title' => 'Başarı Listesi', 'type' => 'leaderboard'],
        'quick_qr' => ['visible' => true, 'span' => 12, 'order' => 110, 'title' => 'Mobil QR Girişi', 'type' => 'qr'],
        'active_classes' => ['visible' => true, 'span' => 6, 'order' => 75, 'title' => 'Aktif Sınıflar', 'type' => 'active_classes'],
    ];

    public function index()
    {
        $user = auth()->user();
        $selectedClassId = (int) request()->query('class_id', 0);

        if ($user?->hasRole('student')) {
            return redirect()->route('student.portal.dashboard');
        }

        $dashboard = Cache::remember('dashboard.teacher.' . ($user?->id ?? 'guest') . '.class.' . $selectedClassId, now()->addSeconds(20), function () use ($user, $selectedClassId) {
            $isAdmin = $user?->hasRole('admin') === true;
            $teacher = null;
            $teacherClassIds = [];

            if (! $isAdmin && $user) {
                $teacher = Teacher::query()->where('user_id', $user->id)->first();
                $teacherClassIds = $teacher
                    ? $teacher->classes()->pluck('school_classes.id')->map(fn ($id) => (int) $id)->all()
                    : [];
            }

            $availableClassIds = $isAdmin
                ? SchoolClass::query()->pluck('id')->map(fn ($id) => (int) $id)->all()
                : $teacherClassIds;

            $activeClassId = $selectedClassId > 0 && in_array($selectedClassId, $availableClassIds, true)
                ? $selectedClassId
                : 0;

            $classTabs = SchoolClass::query()
                ->when(! $isAdmin, fn ($q) => $q->whereIn('id', $teacherClassIds))
                ->select('id', 'name', 'section')
                ->orderBy('name')
                ->orderBy('section')
                ->get()
                ->map(fn ($class) => [
                    'id' => (int) $class->id,
                    'label' => $this->normalizeDashboardText(trim($class->name . '/' . $class->section)),
                ])
                ->values()
                ->all();
            $studentsBase = Student::query()
                ->when(! $isAdmin, fn ($q) => $q->whereIn('school_class_id', $teacherClassIds))
                ->when($activeClassId > 0, fn ($q) => $q->where('school_class_id', $activeClassId));

            $studentIds = (clone $studentsBase)->pluck('id');
            $studentUserIds = (clone $studentsBase)->pluck('user_id');

            $totalStudents = (clone $studentsBase)->count();

            $totalClasses = SchoolClass::query()
                ->when(! $isAdmin, fn ($q) => $q->whereIn('id', $teacherClassIds))
                ->when($activeClassId > 0, fn ($q) => $q->where('id', $activeClassId))
                ->count();

            $totalCourses = Course::query()
                ->whereNull('parent_course_id')
                ->when(! $isAdmin, function ($q) use ($teacher) {
                    $teacherId = (int) ($teacher?->id ?? 0);
                    $q->where('teacher_id', $teacherId);
                })
                ->when($activeClassId > 0, fn ($q) => $q->where('school_class_id', $activeClassId))
                ->count();

            $gradeCount = Grade::query()
                ->when(! $isAdmin, fn ($q) => $q->whereIn('student_id', $studentIds))
                ->count();

            $avgGrade = round((float) Grade::query()
                ->when(! $isAdmin, fn ($q) => $q->whereIn('student_id', $studentIds))
                ->avg('score'), 1);

            // "Ortalama Not / Genel Basari" widget'i sadece manuel girilen
            // Grade kayitlarinin ortalamasini kullaniyordu. Cogu okulda
            // manuel not hic girilmiyor (ilerleme oyun/odev/ders tamamlama
            // uzerinden takip ediliyor) - bu durumda widget her zaman %0
            // gosteriyordu, sistemde gercek ilerleme olsa bile. Artik
            // "ilerleme kaydi olan ogrenci orani" da (odev tamamlama, oyun
            // odevi tamamlama, ders/icerik tamamlama - hepsi ContentProgress/
            // StudentHomeworkProgress/StudentGameAssignmentProgress
            // tablolarindan) ikinci bir sinyal olarak hesaplanip, not
            // girilmemisse bu sinyal kullaniliyor; not da girilmisse ikisinin
            // ortalamasi aliniyor.
            $studentsWithHomeworkProgress = StudentHomeworkProgress::query()
                ->whereIn('student_id', $studentIds)
                ->whereNotNull('completed_at')
                ->distinct()
                ->pluck('student_id');
            $studentsWithGameProgress = StudentGameAssignmentProgress::query()
                ->whereIn('student_id', $studentIds)
                ->whereNotNull('completed_at')
                ->distinct()
                ->pluck('student_id');
            $userIdToStudentId = (clone $studentsBase)->pluck('id', 'user_id');
            $studentsWithContentProgress = ContentProgress::query()
                ->whereIn('user_id', $studentUserIds)
                ->where('completed', true)
                ->distinct()
                ->pluck('user_id')
                ->map(fn ($userId) => $userIdToStudentId[$userId] ?? null)
                ->filter();

            $studentsWithAnyProgress = $studentsWithHomeworkProgress
                ->merge($studentsWithGameProgress)
                ->merge($studentsWithContentProgress)
                ->unique();

            $progressParticipationRate = $totalStudents > 0
                ? ($studentsWithAnyProgress->count() / $totalStudents) * 100
                : 0.0;

            $successSignals = [];
            if ($gradeCount > 0) {
                $successSignals[] = $avgGrade;
            }
            $successSignals[] = $progressParticipationRate;
            $blendedSuccessRate = array_sum($successSignals) / count($successSignals);

            $avgGradeByStudent = Grade::query()
                ->selectRaw('student_id, ROUND(AVG(score), 1) as avg_score')
                ->when(! $isAdmin, fn ($q) => $q->whereIn('student_id', $studentIds))
                ->groupBy('student_id')
                ->pluck('avg_score', 'student_id');

            $activeStudents = StudentTimeStat::query()
                ->when(! $isAdmin, fn ($q) => $q->whereIn('student_id', $studentIds))
                ->whereNotNull('last_seen_at')
                ->where('last_seen_at', '>=', now()->subDay())
                ->count();

            $activeStudentTop3 = StudentTimeStat::query()
                ->with(['student.user'])
                ->when(! $isAdmin, fn ($q) => $q->whereIn('student_id', $studentIds))
                ->whereNotNull('last_seen_at')
                ->where('last_seen_at', '>=', now()->subDay())
                ->orderByDesc('last_seen_at')
                ->limit(3)
                ->get()
                ->map(function (StudentTimeStat $row) {
                    return [
                        'name' => $this->normalizeDashboardText($row->student?->user?->name ?? '-'),
                        'seen_at' => optional($row->last_seen_at)->format('H:i'),
                    ];
                })
                ->values()
                ->all();

            $absentToday = max(0, $totalStudents - $activeStudents);
            $participationRate = $totalStudents > 0 ? (int) round(($activeStudents / $totalStudents) * 100) : 0;
            $progressRate = max(0, min(100, (int) round($blendedSuccessRate)));

            $gradeXpByStudent = Grade::query()
                ->selectRaw('student_id, ROUND(SUM(score)) as xp')
                ->when(! $isAdmin, fn ($q) => $q->whereIn('student_id', $studentIds))
                ->groupBy('student_id')
                ->pluck('xp', 'student_id');

            $contentXpByUser = ContentProgress::query()
                ->selectRaw('user_id, SUM(xp_awarded) as xp')
                ->when(! $isAdmin, fn ($q) => $q->whereIn('user_id', $studentUserIds))
                ->groupBy('user_id')
                ->pluck('xp', 'user_id');

            $quizXpByUser = LiveQuizAnswer::query()
                ->selectRaw('student_user_id as user_id, SUM(xp_earned) as xp')
                ->when(! $isAdmin, fn ($q) => $q->whereIn('student_user_id', $studentUserIds))
                ->groupBy('student_user_id')
                ->pluck('xp', 'user_id');

            // Canli Yarisma'da kazanilan XP daha once hicbir XP toplamina dahil
            // edilmiyordu - ogrenci yarismada XP kazansa bile genel XP'sine
            // yansimiyordu. Diger kaynaklarla (not/icerik/canli quiz) ayni
            // sekilde toplaniyor.
            $competitionXpByUser = CompetitionParticipant::query()
                ->selectRaw('student_user_id as user_id, SUM(xp_earned) as xp')
                ->when(! $isAdmin, fn ($q) => $q->whereIn('student_user_id', $studentUserIds))
                ->groupBy('student_user_id')
                ->pluck('xp', 'user_id');

            $profileXpByUser = UserProfile::query()
                ->selectRaw('user_id, xp')
                ->when(! $isAdmin, fn ($q) => $q->whereIn('user_id', $studentUserIds))
                ->pluck('xp', 'user_id');

            $completedContentCountByUser = ContentProgress::query()
                ->selectRaw('user_id, COUNT(*) as completed_count')
                ->when(! $isAdmin, fn ($q) => $q->whereIn('user_id', $studentUserIds))
                ->where('completed', true)
                ->groupBy('user_id')
                ->pluck('completed_count', 'user_id');

            $students = Student::query()
                ->with(['user', 'schoolClass'])
                ->when(! $isAdmin, fn ($q) => $q->whereIn('school_class_id', $teacherClassIds))
                ->get();

            $studentXpRows = $students->map(function (Student $student) use ($gradeXpByStudent, $contentXpByUser, $quizXpByUser, $competitionXpByUser, $profileXpByUser) {
                $gradeXp = (int) ($gradeXpByStudent[$student->id] ?? 0);
                $contentXp = (int) ($contentXpByUser[$student->user_id] ?? 0);
                $quizXp = (int) ($quizXpByUser[$student->user_id] ?? 0);
                $competitionXp = (int) ($competitionXpByUser[$student->user_id] ?? 0);
                $profileXp = (int) ($profileXpByUser[$student->user_id] ?? 0);
                $computedXp = max(0, $gradeXp + $contentXp + $quizXp + $competitionXp);
                // Avatar magazasinda harcanan XP burada da dusuluyor; boylece
                // admin/ogretmen panelindeki "Basari Listesi" (ilk 5), basari
                // dagilimi grafigi ve toplam XP, ogrenci tarafinda gosterilen
                // guncel (kalan) XP ile birebir tutarli oluyor.
                $xp = max(0, max($computedXp, $profileXp) - (int) ($student->avatar_xp_spent ?? 0));
                $className = $student->schoolClass ? ($student->schoolClass->name . '/' . $student->schoolClass->section) : '-';

                return [
                    'student_id' => $student->id,
                    'user_id' => $student->user_id,
                    'school_class_id' => (int) $student->school_class_id,
                    'name' => $this->normalizeDashboardText($student->user?->name ?? ('user_' . $student->user_id)),
                    'class_name' => $className,
                    'xp' => $xp,
                    'avg_grade' => (float) ($avgGradeByStudent[$student->id] ?? 0),
                ];
            });

            $gradeBuckets = ['Çok İyi (75+)' => 0, 'İyi (50-74)' => 0, 'Orta (25-49)' => 0, 'Düşük (0-24)' => 0];
            foreach ($studentXpRows as $row) {
                $xp = (int) ($row['xp'] ?? 0);
                if ($xp >= 75) $gradeBuckets['Çok İyi (75+)']++;
                elseif ($xp >= 50) $gradeBuckets['İyi (50-74)']++;
                elseif ($xp >= 25) $gradeBuckets['Orta (25-49)']++;
                else $gradeBuckets['Düşük (0-24)']++;
            }
            $gradeTotal = max(1, array_sum($gradeBuckets));
            $gradeDistribution = collect($gradeBuckets)->map(fn ($count, $label) => [
                'label' => $label,
                'count' => (int) $count,
                'percent' => (int) round(($count / $gradeTotal) * 100),
            ])->values()->all();

            $activityBuckets = ['Çok Aktif (20+)' => 0, 'Aktif (11-20)' => 0, 'Orta (6-10)' => 0, 'Pasif (0-5)' => 0];
            foreach ($students as $student) {
                $contentCount = (int) ($completedContentCountByUser[$student->user_id] ?? 0);
                if ($contentCount >= 21) $activityBuckets['Çok Aktif (20+)']++;
                elseif ($contentCount >= 11) $activityBuckets['Aktif (11-20)']++;
                elseif ($contentCount >= 6) $activityBuckets['Orta (6-10)']++;
                else $activityBuckets['Pasif (0-5)']++;
            }
            $activityTotal = max(1, array_sum($activityBuckets));
            $activityDistribution = collect($activityBuckets)->map(fn ($count, $label) => [
                'label' => $label,
                'count' => (int) $count,
                'percent' => (int) round(($count / $activityTotal) * 100),
            ])->values()->all();

            $contentCompletion = $studentXpRows
                ->sortByDesc('xp')
                ->take(5)
                ->values()
                ->map(fn (array $row) => [
                    'label' => $row['name'],
                    'value' => (int) round(min(100, max(0, (int) $row['xp']))),
                ])->all();

            $studentLessonBase = $students
                ->when($activeClassId > 0, fn ($rows) => $rows->where('school_class_id', $activeClassId));

            $studentLessonCompletion = $studentLessonBase
                ->map(function (Student $student) use ($completedContentCountByUser) {
                    return [
                        'label' => $this->normalizeDashboardText($student->user?->name ?? ('user_' . $student->user_id)),
                        'value' => (int) ($completedContentCountByUser[$student->user_id] ?? 0),
                    ];
                })
                ->sortByDesc('value')
                ->take(12)
                ->values()
                ->all();

            $chartWidgets = [
                'success_distribution' => [
                    'title' => 'Başarı Dağılımı',
                    'subtitle' => 'Öğrenci XP verisine göre',
                    'type' => 'donut',
                    'span' => 4,
                    'order' => 85,
                    'zone' => 'grid',
                    'items' => $gradeDistribution,
                ],
                'student_lesson_completion' => [
                    'title' => 'Öğrenci Ders Tamamlama',
                    'subtitle' => 'En çok tamamlayanlar',
                    'type' => 'bar',
                    'span' => 3,
                    'order' => 95,
                    'zone' => 'grid',
                    'items' => $studentLessonCompletion,
                ],
            ];

            $totalXp = (int) $studentXpRows->sum('xp');
            $topStudents = $studentXpRows
                ->when($activeClassId > 0, fn ($rows) => $rows->where('school_class_id', $activeClassId))
                ->sortByDesc('xp')
                ->values()
                ->take(5)
                ->map(function (array $row, int $i) {
                    $row['rank'] = $i + 1;
                    $row['name'] = $this->normalizeDashboardText((string) ($row['name'] ?? '-'));
                    $row['class_name'] = $this->normalizeDashboardText((string) ($row['class_name'] ?? '-'));

                    return $row;
                })
                ->all();

            $classDistribution = Student::query()
                ->join('school_classes', 'students.school_class_id', '=', 'school_classes.id')
                ->when(! $isAdmin, fn ($q) => $q->whereIn('students.school_class_id', $teacherClassIds))
                ->when($activeClassId > 0, fn ($q) => $q->where('students.school_class_id', $activeClassId))
                ->selectRaw("CONCAT(school_classes.name, '/', school_classes.section) as class_name, COUNT(*) as total")
                ->groupBy('school_classes.id', 'school_classes.name', 'school_classes.section')
                ->orderByDesc('total')
                ->get();

            $gradeByClass = Grade::query()
                ->join('students', 'grades.student_id', '=', 'students.id')
                ->join('school_classes', 'students.school_class_id', '=', 'school_classes.id')
                ->when(! $isAdmin, fn ($q) => $q->whereIn('students.school_class_id', $teacherClassIds))
                ->when($activeClassId > 0, fn ($q) => $q->where('students.school_class_id', $activeClassId))
                ->selectRaw("CONCAT(school_classes.name, '/', school_classes.section) as class_name, ROUND(AVG(grades.score), 1) as avg_score")
                ->groupBy('school_classes.id', 'school_classes.name', 'school_classes.section')
                ->orderByDesc('avg_score')
                ->get();

            $xpLeader = $gradeByClass->first();
            $lowActivity = $classDistribution->last();
            $supportClass = $classDistribution->sortBy('total')->first();
            $focusClass = $classDistribution->first();
            $topCompletion = $gradeByClass->first();

            return [
                'headline_name' => $this->normalizeDashboardText($user?->name ?? 'Öğretmen'),
                'selected_class_id' => $activeClassId,
                'class_tabs' => $classTabs,
                'summary' => [
                    'total_students' => $totalStudents,
                    'active_students' => $activeStudents,
                    'active_students_top3' => $activeStudentTop3,
                    'avg_completion' => $progressRate,
                    'total_xp' => $totalXp,
                    'participation' => $participationRate,
                    'progress' => $progressRate,
                    'total_classes' => $totalClasses,
                    'total_courses' => $totalCourses,
                    'absent_today' => $absentToday,
                ],
                'metrics' => [
                    'total_students' => $totalStudents,
                    'active_students' => $activeStudents,
                    'active_students_top3' => $activeStudentTop3,
                    'avg_completion' => $progressRate,
                    'total_xp' => $totalXp,
                    'participation' => $participationRate,
                    'progress' => $progressRate,
                    'total_classes' => $totalClasses,
                    'total_courses' => $totalCourses,
                    'absent_today' => $absentToday,
                ],
                'signals' => [
                    'support' => $supportClass?->class_name ?? '-',
                    'xp_leader' => $xpLeader?->class_name ?? '-',
                    'xp_per_student' => $xpLeader ? (int) round($xpLeader->avg_score) : 0,
                    'focus' => $focusClass?->class_name ?? '-',
                    'focus_value' => $focusClass ? min(100, max(0, (int) round(($focusClass->total / max(1, $totalStudents)) * 100))) : 0,
                    'status' => $totalClasses > 0 ? "{$totalClasses} sınıf izleniyor." : 'Henüz sınıf verisi yok.',
                ],
                'highlights' => [
                    'focus_title' => $activeStudents < max(1, (int) round($totalStudents * 0.4)) ? 'Katılımı artırın' : 'Ritim dengede',
                    'focus_desc' => max(0, $totalStudents - $activeStudents) . ' öğrenci beklemede.',
                    'power_title' => $xpLeader ? "{$xpLeader->class_name} önde" : 'Henüz lider sınıf yok',
                    'power_desc' => $xpLeader ? "Ortalama {$xpLeader->avg_score} puan ile güçlü sinyal veriyor." : 'Not verisi oluştuğunda otomatik hesaplanır.',
                    'rhythm_title' => Grade::query()
                        ->when(! $isAdmin, fn ($q) => $q->whereIn('student_id', $studentIds))
                        ->count() . ' toplam puan girdisi',
                    'rhythm_desc' => $absentToday > 0 ? "Bugün {$absentToday} devamsız var." : 'Devamsızlık sinyali düşük.',
                ],
                'weekly' => [
                    'most_active' => $focusClass?->class_name ?? '-',
                    'best_completion' => $topCompletion?->class_name ?? '-',
                    'xp_leader' => $xpLeader?->class_name ?? '-',
                    'low_activity' => $lowActivity?->class_name ?? '-',
                ],
                'chart_widgets' => $chartWidgets,
                'top_students' => $topStudents,
            ];
        });

        $layout = $this->resolveLayout($user);

        return view('dashboard.index', [
            'dashboard' => $dashboard,
            'dashboardLayout' => $layout,
            'selectedClassId' => $dashboard['selected_class_id'] ?? 0,
        ]);
    }

    /**
     * "Aktif Siniflar" widget'i icin: sadece son 15 dakika icinde en az bir
     * istek atmis (StudentTimeStat.last_seen_at - TrackStudentActiveTime
     * middleware'i tarafindan her istekte guncelleniyor) ogrencisi olan
     * siniflari listeler. Admin tum siniflari, ogretmen sadece kendi
     * siniflarini gorur.
     */
    public function activeClasses(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('admin', 'teacher'), 403);
        $isAdmin = $user->hasRole('admin');

        $teacherClassIds = [];
        if (! $isAdmin) {
            $teacher = Teacher::query()->where('user_id', $user->id)->first();
            $teacherClassIds = $teacher
                ? $teacher->classes()->pluck('school_classes.id')->map(fn ($id) => (int) $id)->all()
                : [];
        }

        $rows = Student::query()
            ->join('student_time_stats', 'student_time_stats.student_id', '=', 'students.id')
            ->join('school_classes', 'school_classes.id', '=', 'students.school_class_id')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->when(! $isAdmin, fn ($q) => $q->whereIn('students.school_class_id', $teacherClassIds))
            ->where('student_time_stats.last_seen_at', '>=', now()->subMinutes(15))
            // Cikis yaptirilan (force_logout_at girildikten sonra ogrenci
            // henuz tekrar giris yapmamis) ogrenciler "aktif" sayilmamali -
            // aksi halde last_seen_at hala eski (cikistan onceki) deger
            // oldugu icin sinif, cikis yaptirildiktan sonra bile listede
            // "aktif" gorunmeye devam ediyordu.
            ->where(function ($q) {
                $q->whereNull('users.force_logout_at')
                    ->orWhereColumn('users.force_logout_at', '<', 'student_time_stats.last_seen_at');
            })
            ->selectRaw('school_classes.id as class_id, school_classes.name, school_classes.section, COUNT(*) as active_count')
            ->groupBy('school_classes.id', 'school_classes.name', 'school_classes.section')
            ->orderBy('school_classes.name')
            ->orderBy('school_classes.section')
            ->get()
            ->map(fn ($row) => [
                'class_id' => (int) $row->class_id,
                'class_name' => $this->normalizeDashboardText($row->name . '/' . $row->section),
                'active_count' => (int) $row->active_count,
            ])
            ->values();

        return response()->json(['classes' => $rows]);
    }

    /**
     * Ders sonrasi tum siniftaki ogrenci hesaplarindan AYNI ANDA cikis
     * yaptirir. Baska siniflarin (ayni anda sisteme girmis olsalar bile)
     * oturumlari etkilenmez - sadece bu sinifin ogrencilerinin User
     * kayitlari isaretlenir. Bkz. app/Http/Middleware/CheckForcedLogout.php.
     */
    public function forceLogoutClass(Request $request, SchoolClass $class): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('admin', 'teacher'), 403);

        if (! $user->hasRole('admin')) {
            $teacher = Teacher::query()->where('user_id', $user->id)->first();
            $ownsClass = $teacher && $teacher->classes()->where('school_classes.id', $class->id)->exists();
            abort_unless($ownsClass, 403);
        }

        $studentUserIds = Student::query()
            ->where('school_class_id', $class->id)
            ->pluck('user_id');

        $affected = User::query()
            ->whereIn('id', $studentUserIds)
            ->update(['force_logout_at' => now()]);

        return response()->json([
            'ok' => true,
            'message' => "{$class->name}/{$class->section} sinifindaki {$affected} ogrenci hesabindan cikis yaptirildi.",
            'affected' => $affected,
        ]);
    }

    public function saveLayout(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('admin', 'teacher'), 403);

        $data = $request->validate([
            'layout' => ['required', 'array'],
            'layout.*.visible' => ['nullable'],
            'layout.*.span' => ['nullable', 'integer', 'between:1,12'],
            'layout.*.order' => ['nullable', 'integer', 'between:1,999'],
            'layout.*.zone' => ['nullable', 'in:grid,sidebar'],
        ]);

        $layout = [];
        foreach ($data['layout'] as $key => $config) {
            $base = self::DEFAULT_WIDGETS[$key] ?? ['title' => $key, 'type' => 'custom'];
            $layout[$key] = array_merge($base, [
                'visible' => (bool) ($config['visible'] ?? false),
                'span' => max(1, min(12, (int) ($config['span'] ?? ($base['span'] ?? 4)))),
                'order' => max(1, min(999, (int) ($config['order'] ?? ($base['order'] ?? 10)))),
                'zone' => in_array(($config['zone'] ?? 'grid'), ['grid', 'sidebar'], true) ? $config['zone'] : 'grid',
            ]);
        }

        $user->dashboard_layout = $layout;
        $user->save();
        $this->forgetDashboardCaches($user);

        return response()->json(['ok' => true, 'layout' => $layout]);
    }

    private function resolveLayout($user): array
    {
        $saved = is_array($user?->dashboard_layout ?? null) ? $user->dashboard_layout : [];
        $merged = [];

        foreach (self::DEFAULT_WIDGETS as $key => $widget) {
            $merged[$key] = array_merge($widget, $saved[$key] ?? []);
            $merged[$key]['zone'] = in_array($merged[$key]['zone'] ?? 'grid', ['grid', 'sidebar'], true)
                ? ($merged[$key]['zone'] ?? 'grid')
                : 'grid';
            if ($key === 'chart_student_lesson_completion') {
                $merged[$key]['zone'] = 'grid';
                $merged[$key]['span'] = max(4, (int) ($merged[$key]['span'] ?? 4));
            }
        }

        foreach ($saved as $key => $widget) {
            if (! isset($merged[$key]) && is_array($widget)) {
                $widgetZone = $widget['zone'] ?? 'grid';
                $widget['zone'] = in_array($widgetZone, ['grid', 'sidebar'], true) ? $widgetZone : 'grid';
                if ($key === 'chart_student_lesson_completion') {
                    $widget['zone'] = 'grid';
                    $widget['span'] = max(4, (int) ($widget['span'] ?? 4));
                }
                $merged[$key] = $widget;
            }
        }

        uasort($merged, fn ($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        return $merged;
    }

    private function forgetDashboardCaches($user): void
    {
        $userId = (int) ($user?->id ?? 0);
        if ($userId <= 0) {
            return;
        }

        $classIds = [0];
        try {
            $classIds = array_merge($classIds, SchoolClass::query()->pluck('id')->map(fn ($id) => (int) $id)->all());
        } catch (\Throwable) {
            // Cache temizliği en iyi çabayla yapılır; ana akış bozulmasın.
        }

        foreach (array_values(array_unique($classIds)) as $classId) {
            Cache::forget('dashboard.teacher.' . $userId . '.class.' . $classId);
        }
    }

    private function normalizeDashboardText(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        // If already valid UTF-8, return as-is.
        // mb_convert_encoding on valid UTF-8 corrupts Turkish chars (ı→Ä±, ş→Å, etc.)
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1254');
        if (is_string($converted) && $converted !== '') {
            return $converted;
        }

        return $value;
    }
}
