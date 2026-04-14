<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ContentProgress;
use App\Models\Grade;
use App\Models\LiveQuizAnswer;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentTimeStat;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user?->hasRole('student')) {
            return redirect()->route('student.portal.dashboard');
        }
        $dashboard = Cache::remember('dashboard.teacher.' . ($user?->id ?? 'guest'), now()->addSeconds(20), function () use ($user) {
            $totalStudents = Student::count();
            $totalCourses = Course::count();
            $totalClasses = SchoolClass::count();
            $avgGrade = round((float) Grade::avg('score'), 1);
            $activeStudents = StudentTimeStat::query()
                ->whereNotNull('last_seen_at')
                ->where('last_seen_at', '>=', now()->subMinutes(10))
                ->count();
            $absentToday = max(0, $totalStudents - $activeStudents);

            $participationRate = $totalStudents > 0 ? (int) round(($activeStudents / $totalStudents) * 100) : 0;
            $progressRate = max(0, min(100, (int) round($avgGrade)));

            $gradeXpByStudent = Grade::query()
                ->selectRaw('student_id, ROUND(SUM(score)) as xp')
                ->groupBy('student_id')
                ->pluck('xp', 'student_id');

            $contentXpByUser = ContentProgress::query()
                ->selectRaw('user_id, SUM(xp_awarded) as xp')
                ->groupBy('user_id')
                ->pluck('xp', 'user_id');

            $quizXpByUser = LiveQuizAnswer::query()
                ->selectRaw('student_user_id as user_id, SUM(xp_earned) as xp')
                ->groupBy('student_user_id')
                ->pluck('xp', 'user_id');

            $profileXpByUser = UserProfile::query()
                ->selectRaw('user_id, xp')
                ->pluck('xp', 'user_id');

            $students = Student::query()->with(['user', 'schoolClass'])->get();
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
                    'name' => $student->user?->name ?? ('user_' . $student->user_id),
                    'class_name' => $className,
                    'xp' => $xp,
                    'avg_grade' => round((float) Grade::where('student_id', $student->id)->avg('score'), 1),
                ];
            });

            $totalXp = (int) $studentXpRows->sum('xp');
            $topStudents = $studentXpRows
                ->sortByDesc('xp')
                ->values()
                ->take(10)
                ->map(function (array $row, int $i) {
                    $row['rank'] = $i + 1;
                    return $row;
                })
                ->all();

            $classDistribution = Student::query()
                ->join('school_classes', 'students.school_class_id', '=', 'school_classes.id')
                ->selectRaw("CONCAT(school_classes.name, '/', school_classes.section) as class_name, COUNT(*) as total")
                ->groupBy('school_classes.id', 'school_classes.name', 'school_classes.section')
                ->orderByDesc('total')
                ->get();

            $gradeByClass = Grade::query()
                ->join('students', 'grades.student_id', '=', 'students.id')
                ->join('school_classes', 'students.school_class_id', '=', 'school_classes.id')
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
                    'rhythm_title' => Grade::count() . ' toplam puan girdisi',
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

        return view('dashboard.index', ['dashboard' => $dashboard]);
    }
}
