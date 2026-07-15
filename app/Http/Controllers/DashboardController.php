<?php

namespace App\Http\Controllers;

use App\Models\ContentProgress;
use App\Models\Course;
use App\Models\Grade;
use App\Models\LiveQuizAnswer;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentTimeStat;
use App\Models\Teacher;
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
        'signals' => ['visible' => true, 'span' => 6, 'order' => 80, 'title' => 'Sınıf Sinyalleri', 'type' => 'signals'],
        'notes' => ['visible' => true, 'span' => 6, 'order' => 90, 'title' => 'Öğretmen Notları', 'type' => 'notes'],
        'leaderboard' => ['visible' => true, 'span' => 12, 'order' => 100, 'title' => 'Başarı Listesi', 'type' => 'leaderboard'],
        'quick_qr' => ['visible' => true, 'span' => 12, 'order' => 110, 'title' => 'Mobil QR Girişi', 'type' => 'qr'],
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
                    'label' => trim($class->name . '/' . $class->section),
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
                ->when(! $isAdmin, function ($q) use ($teacher, $teacherClassIds) {
                    $teacherId = (int) ($teacher?->id ?? 0);
                    $q->where(function ($qq) use ($teacherId, $teacherClassIds) {
                        if ($teacherId > 0) {
                            $qq->where('teacher_id', $teacherId);
                        }

                        if ($teacherClassIds !== []) {
                            $qq->orWhereIn('school_class_id', $teacherClassIds);
                        }
                    });
                })
                ->when($activeClassId > 0, fn ($q) => $q->where('school_class_id', $activeClassId))
                ->count();

            $avgGrade = round((float) Grade::query()
                ->when(! $isAdmin, fn ($q) => $q->whereIn('student_id', $studentIds))
                ->avg('score'), 1);

            $activeStudents = StudentTimeStat::query()
                ->when(! $isAdmin, fn ($q) => $q->whereIn('student_id', $studentIds))
                ->whereNotNull('last_seen_at')
                ->where('last_seen_at', '>=', now()->subMinutes(10))
                ->count();

            $absentToday = max(0, $totalStudents - $activeStudents);
            $participationRate = $totalStudents > 0 ? (int) round(($activeStudents / $totalStudents) * 100) : 0;
            $progressRate = max(0, min(100, (int) round($avgGrade)));

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

            $profileXpByUser = UserProfile::query()
                ->selectRaw('user_id, xp')
                ->when(! $isAdmin, fn ($q) => $q->whereIn('user_id', $studentUserIds))
                ->pluck('xp', 'user_id');

            $students = Student::query()
                ->with(['user', 'schoolClass'])
                ->when(! $isAdmin, fn ($q) => $q->whereIn('school_class_id', $teacherClassIds))
                ->get();

            $studentXpRows = $students->map(function (Student $student) use ($gradeXpByStudent, $contentXpByUser, $quizXpByUser, $profileXpByUser) {
                $gradeXp = (int) ($gradeXpByStudent[$student->id] ?? 0);
                $contentXp = (int) ($contentXpByUser[$student->user_id] ?? 0);
                $quizXp = (int) ($quizXpByUser[$student->user_id] ?? 0);
                $profileXp = (int) ($profileXpByUser[$student->user_id] ?? 0);
                $computedXp = max(0, $gradeXp + $contentXp + $quizXp);
                $xp = max($computedXp, $profileXp);
                $className = $student->schoolClass ? ($student->schoolClass->name . '/' . $student->schoolClass->section) : '-';

                return [
                    'student_id' => $student->id,
                    'user_id' => $student->user_id,
                    'school_class_id' => (int) $student->school_class_id,
                    'name' => $student->user?->name ?? ('user_' . $student->user_id),
                    'class_name' => $className,
                    'xp' => $xp,
                    'avg_grade' => round((float) Grade::where('student_id', $student->id)->avg('score'), 1),
                ];
            });

            $totalXp = (int) $studentXpRows->sum('xp');
            $topStudents = $studentXpRows
                ->when($activeClassId > 0, fn ($rows) => $rows->where('school_class_id', $activeClassId))
                ->sortByDesc('xp')
                ->values()
                ->take(5)
                ->map(function (array $row, int $i) {
                    $row['rank'] = $i + 1;

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
                'headline_name' => $user?->name ?? 'Öğretmen',
                'selected_class_id' => $activeClassId,
                'class_tabs' => $classTabs,
                'summary' => [
                    'total_students' => $totalStudents,
                    'active_students' => $activeStudents,
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

    public function saveLayout(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('admin', 'teacher'), 403);

        $data = $request->validate([
            'layout' => ['required', 'array'],
            'layout.*.visible' => ['nullable'],
            'layout.*.span' => ['nullable', 'integer', 'between:1,12'],
            'layout.*.order' => ['nullable', 'integer', 'between:1,999'],
        ]);

        $layout = [];
        foreach ($data['layout'] as $key => $config) {
            $base = self::DEFAULT_WIDGETS[$key] ?? ['title' => $key, 'type' => 'custom'];
            $layout[$key] = array_merge($base, [
                'visible' => (bool) ($config['visible'] ?? false),
                'span' => max(1, min(12, (int) ($config['span'] ?? ($base['span'] ?? 4)))),
                'order' => max(1, min(999, (int) ($config['order'] ?? ($base['order'] ?? 10)))),
            ]);
        }

        $user->dashboard_layout = $layout;
        $user->save();

        return response()->json(['ok' => true, 'layout' => $layout]);
    }

    private function resolveLayout($user): array
    {
        $saved = is_array($user?->dashboard_layout ?? null) ? $user->dashboard_layout : [];
        $merged = [];

        foreach (self::DEFAULT_WIDGETS as $key => $widget) {
            $merged[$key] = array_merge($widget, $saved[$key] ?? []);
        }

        foreach ($saved as $key => $widget) {
            if (! isset($merged[$key]) && is_array($widget)) {
                $merged[$key] = $widget;
            }
        }

        uasort($merged, fn ($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        return $merged;
    }
}
