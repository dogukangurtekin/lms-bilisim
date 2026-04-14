<?php

namespace App\Services;

use App\Models\ContentProgress;
use App\Models\Course;
use App\Models\CourseHomework;
use App\Models\GameAssignment;
use App\Models\Grade;
use App\Models\LiveQuizAnswer;
use App\Models\Student;
use App\Models\StudentGameAssignmentProgress;
use App\Models\StudentHomeworkProgress;
use App\Models\StudentTimeStat;
use Carbon\Carbon;

class StudentProgressReportService
{
    public function build(Student $student): array
    {
        $student->loadMissing(['user', 'schoolClass', 'currentAvatar', 'badges', 'avatars']);

        $courses = Course::with(['teacher.user', 'schoolClass'])
            ->where('school_class_id', $student->school_class_id)
            ->orderBy('name')
            ->get();

        $courseHomeworks = CourseHomework::withTrashed()
            ->with(['course', 'schoolClass'])
            ->where(function ($q) use ($student) {
                $q->where('school_class_id', $student->school_class_id)
                    ->orWhereNull('school_class_id')
                    ->orWhereHas('course', fn ($cq) => $cq->where('school_class_id', $student->school_class_id));
            })
            ->latest()
            ->get();

        $gameAssignments = GameAssignment::withTrashed()
            ->with(['classes', 'levels'])
            ->where(function ($q) use ($student) {
                $q->whereHas('classes', fn ($cq) => $cq->where('school_classes.id', $student->school_class_id))
                    ->orWhereIn(
                        'id',
                        StudentGameAssignmentProgress::query()
                            ->where('student_id', $student->id)
                            ->pluck('game_assignment_id')
                            ->filter()
                    );
            })
            ->latest()
            ->get();

        $homeworkProgress = StudentHomeworkProgress::where('student_id', $student->id)->get()->keyBy('course_homework_id');
        $completedHomeworkIds = $homeworkProgress
            ->filter(fn ($p) => !empty($p->course_homework_id))
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        if (!empty($completedHomeworkIds)) {
            $progressHomeworks = CourseHomework::withTrashed()
                ->with(['course', 'schoolClass'])
                ->whereIn('id', $completedHomeworkIds)
                ->get();
            $courseHomeworks = $courseHomeworks
                ->concat($progressHomeworks)
                ->unique('id')
                ->sortByDesc('created_at')
                ->values();
        }

        $gameProgress = StudentGameAssignmentProgress::where('student_id', $student->id)->get()->keyBy('game_assignment_id');
        $contentRows = ContentProgress::where('user_id', $student->user_id)->latest()->limit(100)->get();
        $courseProgressRows = ContentProgress::where('user_id', $student->user_id)
            ->where('content_id', 'like', 'course-%')
            ->latest()
            ->get();

        $gradeXp = (int) round((float) Grade::where('student_id', $student->id)->sum('score'));
        $contentXp = (int) ContentProgress::where('user_id', $student->user_id)->sum('xp_awarded');
        $quizXp = (int) LiveQuizAnswer::query()
            ->where('student_user_id', $student->user_id)
            ->sum('xp_earned');
        $quizJoinedCount = (int) LiveQuizAnswer::query()
            ->where('student_user_id', $student->user_id)
            ->distinct('live_quiz_session_id')
            ->count('live_quiz_session_id');
        $totalXp = max(0, $gradeXp + $contentXp);
        $avgGrade = round((float) Grade::where('student_id', $student->id)->avg('score'), 1);

        $courseSlideCount = $courses->filter(fn ($c) => count((array) data_get($c->lesson_payload, 'slides', [])) > 0)->count();
        $completedSlides = ContentProgress::where('user_id', $student->user_id)
            ->where('content_id', 'like', 'course-%')
            ->where('completed', true)
            ->count();

        $totalAssignments = $courseHomeworks->count() + $gameAssignments->count() + $courseSlideCount;
        $completedHomework = $homeworkProgress->filter(fn ($p) => !empty($p->completed_at))->count();
        $completedGames = $gameProgress->filter(fn ($p) => !empty($p->completed_at))->count();
        $completedTotal = $completedHomework + $completedGames + $completedSlides;
        $overallProgress = $totalAssignments > 0 ? (int) round(($completedTotal / $totalAssignments) * 100) : 0;

        $timeStat = StudentTimeStat::where('student_id', $student->id)->first();
        $totalSeconds = (int) ($timeStat?->total_seconds ?? 0);
        $timeText = sprintf(
            '%02d gün %02d saat %02d dk %02d sn',
            floor($totalSeconds / 86400),
            floor(($totalSeconds % 86400) / 3600),
            floor(($totalSeconds % 3600) / 60),
            $totalSeconds % 60
        );

        $students = Student::with(['user', 'schoolClass'])->get();
        $xpMap = [];
        foreach ($students as $s) {
            $sx = (int) round((float) Grade::where('student_id', $s->id)->sum('score'));
            $cx = (int) ContentProgress::where('user_id', $s->user_id)->sum('xp_awarded');
            $xpMap[$s->id] = max(0, $sx + $cx);
        }

        $schoolRankPos = collect($xpMap)->sortDesc()->keys()->search($student->id);
        $schoolRank = $schoolRankPos === false ? null : ($schoolRankPos + 1);
        $gradePeers = $students->filter(fn ($s) => $s->schoolClass?->name === $student->schoolClass?->name);
        $classRankPos = $gradePeers->mapWithKeys(fn ($s) => [$s->id => $xpMap[$s->id] ?? 0])->sortDesc()->keys()->search($student->id);
        $classRank = $classRankPos === false ? null : ($classRankPos + 1);

        $taskTrend = [];
        $appTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $next = (clone $day)->addDay();
            $completedHomeworkDaily = (int) StudentHomeworkProgress::where('student_id', $student->id)
                ->whereNotNull('completed_at')
                ->whereBetween('completed_at', [$day, $next])
                ->count();
            $completedGameDaily = (int) StudentGameAssignmentProgress::where('student_id', $student->id)
                ->whereNotNull('completed_at')
                ->whereBetween('completed_at', [$day, $next])
                ->count();
            $completedAppHomeworkDaily = (int) StudentHomeworkProgress::where('student_id', $student->id)
                ->whereNotNull('completed_at')
                ->whereBetween('completed_at', [$day, $next])
                ->whereHas('homework', fn ($q) => $q->whereIn('assignment_type', ['game', 'application']))
                ->count();

            $taskTrend[] = ['label' => $day->format('d.m'), 'value' => ($completedHomeworkDaily + $completedGameDaily)];
            $appTrend[] = ['label' => $day->format('d.m'), 'value' => ($completedGameDaily + $completedAppHomeworkDaily)];
        }

        $lessonHomeworkTotal = $courseHomeworks->filter(fn ($h) => (string) ($h->assignment_type ?? 'lesson') === 'lesson')->count();
        $lessonHomeworkCompleted = $courseHomeworks
            ->filter(fn ($h) => (string) ($h->assignment_type ?? 'lesson') === 'lesson')
            ->filter(fn ($h) => !empty($homeworkProgress->get($h->id)?->completed_at))
            ->count();

        $computeTotal = 0;
        $computeCompleted = 0;
        $block3dTotal = 0;
        $block3dCompleted = 0;
        $blockTotal = 0;
        $blockCompleted = 0;
        $activityTotal = 0;
        $activityCompleted = 0;

        foreach ($gameAssignments as $a) {
            $slug = strtolower((string) ($a->game_slug ?? ''));
            $name = strtolower((string) ($a->game_name ?? ''));
            $isCompleted = !empty($gameProgress->get($a->id)?->completed_at);

            if (str_contains($slug, 'compute') || str_contains($name, 'compute')) {
                $computeTotal++;
                if ($isCompleted) {
                    $computeCompleted++;
                }
                continue;
            }

            if (str_contains($slug, '3d') || str_contains($name, '3d')) {
                $block3dTotal++;
                if ($isCompleted) {
                    $block3dCompleted++;
                }
                continue;
            }

            if (str_contains($slug, 'block') || str_contains($name, 'blok') || str_contains($name, 'block') || str_contains($slug, 'lightbot')) {
                $blockTotal++;
                if ($isCompleted) {
                    $blockCompleted++;
                }
                continue;
            }

            $activityTotal++;
            if ($isCompleted) {
                $activityCompleted++;
            }
        }

        $pct = function (int $done, int $all): int {
            if ($all <= 0) {
                return 0;
            }

            return (int) round(($done / $all) * 100);
        };

        $categoryChart = [
            ['label' => 'Ödev', 'value' => $pct($lessonHomeworkCompleted, $lessonHomeworkTotal), 'color' => '#3b82f6'],
            ['label' => 'Etkinlik', 'value' => $pct($activityCompleted, $activityTotal), 'color' => '#14b8a6'],
            ['label' => 'Blok', 'value' => $pct($blockCompleted, $blockTotal), 'color' => '#6366f1'],
            ['label' => '3D Blok', 'value' => $pct($block3dCompleted, $block3dTotal), 'color' => '#8b5cf6'],
            ['label' => 'Compute', 'value' => $pct($computeCompleted, $computeTotal), 'color' => '#7c3aed'],
            ['label' => 'Derslerim', 'value' => $pct($completedSlides, $courseSlideCount), 'color' => '#10b981'],
        ];

        $analysis = [];
        $analysis[] = $overallProgress >= 80 ? 'Genel görev tamamlama oranı çok iyi seviyede.' : 'Genel görev tamamlama oranı artırılmalı.';
        $analysis[] = $totalXp >= 300 ? 'XP performansı sınıf ortalamasının üzerinde.' : 'XP gelişimi için düzenli görev tamamlama önerilir.';
        $analysis[] = $schoolRank === 1 ? 'Öğrenci okul genelinde birinci sıradadır.' : 'Okul sıralamasında üst sıralara çıkmak için haftalık düzenli çalışma önerilir.';
        $analysis[] = $classRank === 1 ? 'Öğrenci sınıfında birinci sıradadır.' : 'Sınıf sıralamasında yükseliş için eksik görevlerin tamamlanması gerekir.';
        $analysis[] = $totalSeconds >= 7200 ? 'Sistemde geçirilen süre öğrenme istikrarını destekliyor.' : 'Sistemde geçirilen süre artırılarak ilerleme hızlandırılabilir.';

        $recommendations = [];
        if ($overallProgress < 60) {
            $recommendations[] = 'Bu hafta için günlük en az 1 görev tamamlama hedefi belirleyin.';
        } else {
            $recommendations[] = 'Mevcut tempo korunarak haftalık 1 ek zorluk görevi eklenebilir.';
        }
        if ($totalXp < 300) {
            $recommendations[] = 'XP artışı için önce kısa seviyeli oyun/uygulama ödevleri tamamlanmalı.';
        } else {
            $recommendations[] = 'Yüksek XP performansı için ileri seviye içeriklere geçiş planlanabilir.';
        }
        if ($avgGrade < 70) {
            $recommendations[] = 'Ders slaytlarının tekrar edilmesi ve haftalık mini tekrar testi önerilir.';
        } else {
            $recommendations[] = 'Not ortalamasını korumak için düzenli tekrar ve soru çözümü sürdürülmeli.';
        }
        if ($totalSeconds < 3600) {
            $recommendations[] = 'Sistemde günlük 20-30 dakika planlı çalışma uygulanmalı.';
        }
        if ($classRank !== null && $classRank > 3) {
            $recommendations[] = 'Sınıf sıralaması için tamamlanmamış ders ödevleri önceliklendirilmeli.';
        }

        $courseItems = [];
        foreach ($courseHomeworks as $h) {
            $p = $homeworkProgress->get($h->id);
            $courseItems[] = [
                'course_name' => $h->course?->name ?? '-',
                'title' => $h->title,
                'due_date' => $h->due_date,
                'status' => $p?->completed_at ? 'Tamamlandı' : ($p?->started_at ? 'Devam Ediyor' : 'Bekliyor'),
                'xp' => (int) ($p?->xp_awarded ?? 0),
                'sort_date' => $p?->completed_at ?? $p?->started_at ?? $h->created_at,
            ];
        }

        $courseMap = $courses->keyBy('id');
        foreach ($courseProgressRows as $row) {
            if (!preg_match('/^course-(\d+)$/', (string) $row->content_id, $m)) {
                continue;
            }
            $course = $courseMap->get((int) $m[1]);
            if (!$course) {
                continue;
            }
            $courseItems[] = [
                'course_name' => $course->name,
                'title' => 'Ders Slayt Görevi',
                'due_date' => null,
                'status' => $row->completed ? 'Tamamlandı' : 'Devam Ediyor',
                'xp' => (int) ($row->xp_awarded ?? 0),
                'sort_date' => $row->updated_at ?? $row->created_at,
            ];
        }

        $courseItems = collect($courseItems)
            ->sortByDesc('sort_date')
            ->values();

        return [
            'kpi' => [
                'total_xp' => $totalXp,
                'grade_avg' => $avgGrade,
                'quiz_joined_count' => $quizJoinedCount,
                'quiz_total_xp' => $quizXp,
                'completed_total' => $completedTotal,
                'total_assignments' => $totalAssignments,
                'overall_progress' => $overallProgress,
                'school_rank' => $schoolRank,
                'class_rank' => $classRank,
                'badge_count' => $student->badges->count(),
                'avatar_count' => $student->avatars->count(),
                'time_text' => $timeText,
                'time_seconds' => $totalSeconds,
            ],
            'courses' => $courses,
            'course_homeworks' => $courseHomeworks,
            'game_assignments' => $gameAssignments,
            'homework_progress' => $homeworkProgress,
            'game_progress' => $gameProgress,
            'content_rows' => $contentRows,
            'task_trend' => $taskTrend,
            'app_trend' => $appTrend,
            'category_chart' => $categoryChart,
            'analysis' => $analysis,
            'recommendations' => $recommendations,
            'course_items' => $courseItems,
        ];
    }
}
