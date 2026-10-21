<?php

namespace App\Services;

use App\Models\ActivityAttempt;
use App\Models\Badge;
use App\Models\CompetitionParticipant;
use App\Models\ContentProgress;
use App\Models\Course;
use App\Models\CourseHomework;
use App\Models\GameAssignment;
use App\Models\Grade;
use App\Models\LiveQuizAnswer;
use App\Models\RaceResult;
use App\Models\Student;
use App\Models\StudentGameAssignmentProgress;
use App\Models\StudentHomeworkProgress;
use App\Models\StudentTimeStat;
use App\Services\LessonPresentation\SlidePresentationService;
use Carbon\Carbon;

class StudentProgressReportService
{
    public function __construct(
        private StudentXpService $xpService,
        private SlidePresentationService $presentation,
    ) {}

    public function build(Student $student): array
    {
        $student->loadMissing(['user', 'schoolClass', 'currentAvatar', 'badges', 'avatars']);

        $courses = Course::with(['teacher.user', 'schoolClass'])
            ->where(function ($q) use ($student) {
                $q->where('school_class_id', $student->school_class_id)
                    ->orWhereExists(function ($sq) use ($student) {
                        $sq->selectRaw('1')
                            ->from('course_homeworks')
                            ->whereColumn('course_homeworks.course_id', 'courses.id')
                            ->where('course_homeworks.school_class_id', $student->school_class_id)
                            ->where('course_homeworks.assignment_type', 'lesson')
                            ->whereNull('course_homeworks.deleted_at');
                    });
            })
            ->orderBy('name')
            ->get();

        $visibleHomeworkIds = StudentHomeworkProgress::query()
            ->where('student_id', $student->id)
            ->pluck('course_homework_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $courseHomeworks = CourseHomework::withTrashed()
            ->with(['course', 'schoolClass'])
            ->where(function ($q) use ($visibleHomeworkIds) {
                if ($visibleHomeworkIds !== []) {
                    $q->whereIn('id', $visibleHomeworkIds);
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->latest()
            ->get();

        $visibleGameIds = StudentGameAssignmentProgress::query()
            ->where('student_id', $student->id)
            ->pluck('game_assignment_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $gameAssignments = GameAssignment::withTrashed()
            ->with(['classes', 'levels'])
            ->where(function ($q) use ($visibleGameIds) {
                if ($visibleGameIds !== []) {
                    $q->whereIn('id', $visibleGameIds);
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->latest()
            ->get();

        $homeworkProgress = StudentHomeworkProgress::where('student_id', $student->id)->get()->keyBy('course_homework_id');
        $completedHomeworkIds = $homeworkProgress
            ->filter(fn ($p) => ! empty($p->course_homework_id))
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        if (! empty($completedHomeworkIds)) {
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
            ->get()
            ->unique('content_id')
            ->values();

        $gradeXp = (int) round((float) Grade::where('student_id', $student->id)->sum('score'));
        $contentXp = (int) ContentProgress::where('user_id', $student->user_id)->sum('xp_awarded');
        $quizXp = (int) LiveQuizAnswer::query()
            ->where('student_user_id', $student->user_id)
            ->sum('xp_earned');
        $quizJoinedCount = \App\Models\LiveQuizSession::joinedCountForUser((int) $student->user_id);
        // Kategori grafiği için: katıldığı quizlerden bitmiş olanların sayısı.
        $quizSessionIdsForChart = \App\Models\LiveQuizParticipant::query()
            ->where('student_user_id', $student->user_id)
            ->pluck('live_quiz_session_id')
            ->merge(LiveQuizAnswer::query()->where('student_user_id', $student->user_id)->pluck('live_quiz_session_id'))
            ->unique()
            ->values();
        $quizCompletedCount = $quizSessionIdsForChart->isEmpty()
            ? 0
            : (int) \App\Models\LiveQuizSession::withTrashed()->whereIn('id', $quizSessionIdsForChart)->where('status', 'finished')->count();
        $quizCompletedCount = min($quizCompletedCount, max($quizJoinedCount, $quizCompletedCount));
        // Canli Yarisma'da kazanilan XP daha once hicbir yerde ogrencinin
        // toplam XP'sine dahil edilmiyordu; Canli Quiz ile ayni sekilde
        // (katilim sayisi + kazanilan XP) burada da takip ediliyor.
        $competitionParticipantRows = CompetitionParticipant::query()
            ->where('student_user_id', $student->user_id)
            ->get();
        $competitionXp = (int) $competitionParticipantRows->sum('xp_earned');
        $keyboardRaceRows = RaceResult::query()
            ->where('user_id', $student->user_id)
            ->where(fn ($q) => $q->whereNull('is_spectator')->orWhere('is_spectator', false))
            ->get();
        $keyboardRaceXp = (int) RaceResult::query()
            ->where('user_id', $student->user_id)
            ->sum('xp_earned');
        // Klavye yarisi da canli yarisma sayilir: katilim ve tamamlama sayilarina dahil.
        $competitionJoinedCount = $competitionParticipantRows
            ->unique('competition_room_id')
            ->count()
            + $keyboardRaceRows->unique('room_id')->count();
        $competitionCompletedCount = $competitionParticipantRows
            ->filter(fn ($participant) => ! empty($participant->finished_at_ms) || (float) $participant->progress_percent >= 100)
            ->unique('competition_room_id')
            ->count()
            + $keyboardRaceRows
                ->filter(fn ($race) => ! empty($race->finished_at) || (float) $race->progress >= 100)
                ->unique('room_id')
                ->count();

        $dailyAttemptRows = ActivityAttempt::query()
            ->with(['answers.activityQuestion'])
            ->where('user_id', $student->user_id)
            ->whereHas('activity', fn ($q) => $q->where('type', 'daily_task'))
            ->latest('submitted_at')
            ->limit(60)
            ->get();
        $dailyAttemptCount = $dailyAttemptRows->count();
        $dailyCorrectCount = 0;
        $dailyWrongCount = 0;
        $dailyFullCorrectCount = 0;
        foreach ($dailyAttemptRows as $attemptRow) {
            $questionsCount = (int) $attemptRow->answers->count();
            $correctCount = (int) $attemptRow->answers->filter(fn ($answer) => (float) $answer->awarded_points > 0)->count();
            $wrongCount = max(0, $questionsCount - $correctCount);
            $dailyCorrectCount += $correctCount;
            $dailyWrongCount += $wrongCount;
            if ($questionsCount > 0 && $correctCount === $questionsCount) {
                $dailyFullCorrectCount++;
            }
        }
        $dailySuccessRate = $dailyAttemptCount > 0 ? (int) round(($dailyFullCorrectCount / $dailyAttemptCount) * 100) : 0;

        // Rapordaki siralama (asagida) avatar harcamasi dusulerek
        // hesaplandigindan, ayni raporun basligindaki XP de tutarli olmasi
        // icin ayni sekilde netleniyor.
        $totalXp = $this->xpService->available($student);
        $avgGrade = round((float) Grade::where('student_id', $student->id)->avg('score'), 1);

        $completedLessonRows = ContentProgress::where('user_id', $student->user_id)
            ->where('content_id', 'like', 'course-%')
            ->where('completed', true)
            ->latest()
            ->get()
            ->unique('content_id')
            ->values();
        $completedSlides = $completedLessonRows->count();

        // İlerleme oranı öğrenci paneliyle aynı aktif atama kümesinden hesaplanır.
        // Yalnızca progress kaydı olan içerikleri saymak, hiç başlanmamış bekleyen
        // ders ve ödevleri dışarıda bırakıp raporu hatalı biçimde %100 gösterebilir.
        // Silinmiş olsa bile öğrencinin tamamladığı ödev/ders ataması rapordan eksilmez.
        $completedHwIds = $homeworkProgress->filter(fn ($p) => ! empty($p->completed_at))->keys()->map(fn ($id) => (int) $id)->all();
        $completedLessonCourseIds = $courseProgressRows->filter(fn ($p) => (bool) $p->completed)
            ->map(fn ($p) => (int) str_replace('course-', '', (string) $p->content_id))->filter()->values()->all();
        $activeCourseHomeworks = CourseHomework::withTrashed()
            ->where('school_class_id', $student->school_class_id)
            ->where(function ($q) use ($completedHwIds, $completedLessonCourseIds): void {
                $q->whereNull('deleted_at')
                    ->orWhereIn('id', $completedHwIds)
                    ->orWhere(fn ($l) => $l->where('assignment_type', 'lesson')->whereIn('course_id', $completedLessonCourseIds));
            })
            ->get();
        $assignmentHomeworks = $activeCourseHomeworks
            ->filter(fn ($homework) => in_array((string) $homework->assignment_type, ['homework', 'game', 'application'], true));
        $gameHomeworkIds = $assignmentHomeworks
            ->filter(fn ($homework) => in_array((string) $homework->assignment_type, ['game', 'application'], true))
            ->pluck('id');
        $lessonCourseIds = $activeCourseHomeworks
            ->filter(fn ($homework) => (string) $homework->assignment_type === 'lesson')
            ->pluck('course_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $startedGameAssignmentIds = $gameProgress->keys()->map(fn ($id) => (int) $id)->values();
        $currentGameAssignments = GameAssignment::withTrashed()
            ->whereHas('classes', fn ($query) => $query->where('school_classes.id', $student->school_class_id))
            ->where(function ($query) use ($startedGameAssignmentIds): void {
                $query->whereNull('deleted_at')->orWhereIn('id', $startedGameAssignmentIds);
            })
            ->get();

        $completedGameAssignmentCount = $gameProgress
            ->only($currentGameAssignments->pluck('id')->all())
            ->filter(fn ($progress) => ! empty($progress->completed_at))
            ->count();
        $completedCourseHomeworkCount = $homeworkProgress
            ->only($assignmentHomeworks->pluck('id')->all())
            ->filter(fn ($progress) => ! empty($progress->completed_at))
            ->count();
        $completedCourseGameHomeworkCount = $homeworkProgress
            ->only($gameHomeworkIds->all())
            ->filter(fn ($progress) => ! empty($progress->completed_at))
            ->count();

        $assignedCourses = Course::query()
            ->with('subCourses:id,parent_course_id')
            ->whereIn('id', $lessonCourseIds)
            ->whereNull('parent_course_id')
            ->get();
        $courseProgressByContentId = $courseProgressRows->keyBy('content_id');
        $completedCourseAssignments = $assignedCourses->filter(function (Course $course) use ($courseProgressByContentId): bool {
            $requiredCourseIds = collect([$course->id])
                ->concat($course->subCourses->pluck('id'))
                ->map(fn ($id) => (int) $id)
                ->unique();

            return $requiredCourseIds->every(
                fn ($courseId) => (bool) $courseProgressByContentId->get('course-'.$courseId)?->completed
            );
        })->count();

        $gameHomeworkCount = $gameHomeworkIds->count();
        $totalAssignments = $currentGameAssignments->count()
            + $gameHomeworkCount
            + $lessonCourseIds->count()
            + $assignmentHomeworks->count();
        $completedTotal = $completedGameAssignmentCount
            + $completedCourseGameHomeworkCount
            + $completedCourseAssignments
            + $completedCourseHomeworkCount;
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

        // Avatar magazasinda harcanan XP siralamadan dusuluyor; boylece bu
        // rapordaki okul/sinif siralamasi, admin/ogretmen panelindeki
        // "Basari Listesi" ve ogrencinin kendi anasayfasindaki guncel
        // (kalan) XP ile tutarli kaliyor.
        $students = Student::with(['user', 'schoolClass'])->get();
        $xpMap = $this->xpService->availableFor($students);

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
                ->whereHas('homework', fn ($q) => $q->withTrashed()->whereIn('assignment_type', ['game', 'application']))
                ->count();

            $taskTrend[] = ['label' => $day->format('d.m'), 'value' => ($completedHomeworkDaily + $completedGameDaily)];
            $appTrend[] = ['label' => $day->format('d.m'), 'value' => ($completedGameDaily + $completedAppHomeworkDaily)];
        }

        $lessonHomeworkTotal = $courseHomeworks->filter(fn ($h) => (string) ($h->assignment_type ?? 'lesson') === 'lesson')->count();
        $lessonHomeworkCompleted = $courseHomeworks
            ->filter(fn ($h) => (string) ($h->assignment_type ?? 'lesson') === 'lesson')
            ->filter(fn ($h) => ! empty($homeworkProgress->get($h->id)?->completed_at))
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
            $isCompleted = ! empty($gameProgress->get($a->id)?->completed_at);

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
            [
                'label' => 'Ödev',
                'value' => $pct($lessonHomeworkCompleted, $lessonHomeworkTotal),
                'done' => $lessonHomeworkCompleted,
                'total' => $lessonHomeworkTotal,
                'color' => '#3b82f6',
            ],
            [
                'label' => 'Etkinlik',
                'value' => $pct($activityCompleted, $activityTotal),
                'done' => $activityCompleted,
                'total' => $activityTotal,
                'color' => '#14b8a6',
            ],
            [
                'label' => 'Blok',
                'value' => $pct($blockCompleted, $blockTotal),
                'done' => $blockCompleted,
                'total' => $blockTotal,
                'color' => '#6366f1',
            ],
            [
                'label' => '3D Blok',
                'value' => $pct($block3dCompleted, $block3dTotal),
                'done' => $block3dCompleted,
                'total' => $block3dTotal,
                'color' => '#8b5cf6',
            ],
            [
                'label' => 'Compute',
                'value' => $pct($computeCompleted, $computeTotal),
                'done' => $computeCompleted,
                'total' => $computeTotal,
                'color' => '#7c3aed',
            ],
            [
                'label' => 'Derslerim',
                'value' => $pct($completedCourseAssignments, $assignedCourses->count()),
                'done' => $completedCourseAssignments,
                'total' => $assignedCourses->count(),
                'color' => '#10b981',
            ],
            [
                'label' => 'Günlük Egzersiz',
                // Yalnızca katıldığı egzersiz sayısı gösterilir (oran değil).
                'value' => $dailyAttemptCount > 0 ? 100 : 0,
                'done' => $dailyAttemptCount,
                'total' => $dailyAttemptCount,
                'caption' => $dailyAttemptCount . ' egzersiz',
                'color' => '#f59e0b',
            ],
            [
                'label' => 'Canlı Quiz',
                // Yalnızca katıldığı quiz sayısı gösterilir (4/5 gibi oran değil).
                'value' => max($quizJoinedCount, $quizCompletedCount) > 0 ? 100 : 0,
                'done' => max($quizJoinedCount, $quizCompletedCount),
                'total' => max($quizJoinedCount, $quizCompletedCount),
                'caption' => max($quizJoinedCount, $quizCompletedCount) . ' quiz',
                'color' => '#06b6d4',
            ],
            [
                'label' => 'Canlı Yarışma',
                // Yalnızca katıldığı yarışma sayısı gösterilir (4/5 gibi oran değil).
                'value' => max($competitionJoinedCount, $competitionCompletedCount) > 0 ? 100 : 0,
                'done' => max($competitionJoinedCount, $competitionCompletedCount),
                'total' => max($competitionJoinedCount, $competitionCompletedCount),
                'caption' => max($competitionJoinedCount, $competitionCompletedCount) . ' yarışma',
                'color' => '#ef4444',
            ],
        ];

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
        $courseRows = $contentRows
            ->filter(function ($row) {
                $contentId = (string) $row->content_id;
                $payload = (array) ($row->payload ?? []);
                $source = (string) data_get($payload, 'source', '');

                return str_starts_with($contentId, 'course-')
                    || str_starts_with($contentId, 'homework-')
                    || $source === 'course_slide'
                    || $source === 'course_homework'
                    || filled(data_get($payload, 'course_name'))
                    || filled(data_get($payload, 'lesson_title'));
            })
            ->unique('content_id')
            ->values();

        $reportCourseIds = $courseRows
            ->map(function ($row): int {
                return preg_match('/^course-(\d+)$/', (string) $row->content_id, $matches)
                    ? (int) $matches[1]
                    : 0;
            })
            ->filter()
            ->unique()
            ->values();
        $reportCourses = Course::withTrashed()->whereIn('id', $reportCourseIds)->get()->keyBy('id');

        foreach ($courseRows as $row) {
            $payload = (array) ($row->payload ?? []);
            $courseName = trim((string) data_get($payload, 'course_name', ''));
            $lessonTitle = trim((string) data_get($payload, 'lesson_title', ''));
            $courseId = 0;
            if (preg_match('/^(?:course|homework)-(\d+)$/', (string) $row->content_id, $m)) {
                $courseId = (int) $m[1];
            }
            $fallbackCourseName = $courseName !== '' ? $courseName : ($lessonTitle !== '' ? $lessonTitle : 'Ders #'.($courseId > 0 ? $courseId : substr((string) $row->content_id, -6)));
            $storedQuestionTotal = max(0, (int) data_get($payload, 'question_total', 0));
            $canonicalQuestionTotal = str_starts_with((string) $row->content_id, 'course-') && $reportCourses->has($courseId)
                ? $this->presentation->questionCount($reportCourses->get($courseId))
                : $storedQuestionTotal;
            $storedCorrectQuestions = max(0, (int) data_get(
                $payload,
                'correct_questions',
                data_get($payload, 'solved_questions', 0)
            ));
            $storedWrongQuestions = max(0, (int) data_get($payload, 'wrong_questions', 0));
            $answeredQuestionTotal = $storedCorrectQuestions + $storedWrongQuestions;
            // Eski ders kayitlarinda ozet slaydi da soru sanilip question_total bir
            // fazla yazilabiliyordu (ornegin 5 dogru + 0 yanlis, toplam 6). Sonuc
            // sayilari varsa bunlar tamamlanan denemenin guvenilir kaynagidir.
            $questionTotal = $answeredQuestionTotal > 0
                ? $answeredQuestionTotal
                : $canonicalQuestionTotal;
            $correctQuestions = min($questionTotal, $storedCorrectQuestions);
            $wrongQuestions = min(max(0, $questionTotal - $correctQuestions), $storedWrongQuestions);

            $courseItems[] = [
                'course_name' => $fallbackCourseName,
                'title' => $lessonTitle !== '' ? $lessonTitle : $fallbackCourseName,
                'display_title' => $lessonTitle !== '' ? $lessonTitle : $fallbackCourseName,
                'due_date' => null,
                'assignment_type' => 'lesson_slide',
                'status' => 'Tamamlandı',
                'xp' => (int) ($row->xp_awarded ?? 0),
                'solved_questions' => (int) data_get($payload, 'solved_questions', 0),
                'question_total' => $questionTotal,
                'correct_questions' => $correctQuestions,
                'wrong_questions' => $wrongQuestions,
                'sort_date' => $row->updated_at ?? $row->created_at,
            ];
        }

        $courseItems = collect($courseItems)
            ->sortByDesc('sort_date')
            ->values();

        // Canli quiz, canli yarisma ve gunluk egzersiz katilimlari karnede
        // sadece kutucuk sayisi olarak degil, tek tek satir olarak da listelenir.
        $liveItems = collect();

        $quizSessionIds = \App\Models\LiveQuizParticipant::query()
            ->where('student_user_id', $student->user_id)
            ->pluck('live_quiz_session_id')
            ->merge(LiveQuizAnswer::query()->where('student_user_id', $student->user_id)->pluck('live_quiz_session_id'))
            ->unique()
            ->values();
        if ($quizSessionIds->isNotEmpty()) {
            $quizSessions = \App\Models\LiveQuizSession::withTrashed()
                ->with('quiz')
                ->whereIn('id', $quizSessionIds)
                ->get();
            $quizAnswersBySession = LiveQuizAnswer::query()
                ->where('student_user_id', $student->user_id)
                ->whereIn('live_quiz_session_id', $quizSessionIds)
                ->get()
                ->groupBy('live_quiz_session_id');
            foreach ($quizSessions as $quizSession) {
                $answers = $quizAnswersBySession->get($quizSession->id, collect());
                $correct = $answers->where('is_correct', true)->count();
                $answered = $answers->whereNotNull('selected_answer')->count();
                $liveItems->push([
                    'kind' => 'Canlı Quiz',
                    'title' => (string) ($quizSession->quiz?->title ?? 'Canlı Quiz'),
                    'status' => $quizSession->status === 'finished' ? 'Tamamlandı' : 'Katıldı',
                    'xp' => (int) $answers->sum('xp_earned'),
                    'result' => $answered > 0 ? $correct.'/'.max(0, $answered - $correct).' ('.$answered.' soru)' : '-',
                    'sort_date' => $quizSession->finished_at_ms
                        ? Carbon::createFromTimestampMs((int) $quizSession->finished_at_ms)
                        : $quizSession->created_at,
                ]);
            }
        }

        $competitionRooms = \App\Models\CompetitionRoom::query()
            ->whereIn('id', $competitionParticipantRows->pluck('competition_room_id')->unique())
            ->get()
            ->keyBy('id');
        foreach ($competitionParticipantRows->unique('competition_room_id') as $participant) {
            $room = $competitionRooms->get($participant->competition_room_id);
            $finished = ! empty($participant->finished_at_ms) || (float) $participant->progress_percent >= 100;
            $liveItems->push([
                'kind' => 'Canlı Yarışma',
                'title' => (string) ($room?->game_name ?: 'Canlı Yarışma'),
                'status' => $finished ? 'Tamamlandı' : 'Katıldı',
                'xp' => (int) $participant->xp_earned,
                'result' => '%'.(int) round((float) $participant->progress_percent),
                'sort_date' => $participant->finished_at_ms
                    ? Carbon::createFromTimestampMs((int) $participant->finished_at_ms)
                    : ($room?->started_at_ms ? Carbon::createFromTimestampMs((int) $room->started_at_ms) : $participant->created_at),
            ]);
        }

        foreach ($dailyAttemptRows as $attemptRow) {
            $questionsCount = (int) $attemptRow->answers->count();
            $correctCount = (int) $attemptRow->answers->filter(fn ($answer) => (float) $answer->awarded_points > 0)->count();
            $liveItems->push([
                'kind' => 'Günlük Egzersiz',
                'title' => (string) ($attemptRow->activity?->title ?? 'Günlük Egzersiz'),
                'status' => $attemptRow->submitted_at ? 'Tamamlandı' : 'Devam Ediyor',
                'xp' => (int) round((float) $attemptRow->score),
                'result' => $questionsCount > 0 ? $correctCount.'/'.max(0, $questionsCount - $correctCount).' ('.$questionsCount.' soru)' : '-',
                'sort_date' => $attemptRow->submitted_at ?? $attemptRow->created_at,
            ]);
        }

        $liveItems = $liveItems->sortByDesc('sort_date')->values();

        // Ogrencinin sistemde yaptigi her seyin tek, tarih sirali dokumu:
        // ders, odev, oyun/uygulama, canli quiz, canli yarisma, gunluk egzersiz,
        // klavye yarisi. Gelisim Karnem sayfasi ve detayli rapor bunu kullanir.
        $activityLog = collect();

        foreach ($courseItems as $item) {
            $activityLog->push([
                'kind' => 'Ders',
                'title' => (string) ($item['course_name'] ?? $item['title'] ?? 'Ders'),
                'status' => 'Tamamlandı',
                'xp' => (int) ($item['xp'] ?? 0),
                'result' => ((int) ($item['question_total'] ?? 0)) > 0
                    ? ($item['correct_questions'] ?? 0).'/'.($item['wrong_questions'] ?? 0).' ('.$item['question_total'].' soru)'
                    : '-',
                'sort_date' => $item['sort_date'] ?? null,
            ]);
        }

        $homeworkTypeLabels = ['homework' => 'Ödev', 'lesson' => 'Ders Ödevi', 'game' => 'Oyun Ödevi', 'application' => 'Uygulama Ödevi'];
        $progressHomeworks = CourseHomework::withTrashed()
            ->with('course')
            ->whereIn('id', $homeworkProgress->keys())
            ->get()
            ->keyBy('id');
        foreach ($homeworkProgress as $homeworkId => $progress) {
            $homework = $progressHomeworks->get((int) $homeworkId);
            $activityLog->push([
                'kind' => $homeworkTypeLabels[(string) ($homework?->assignment_type ?? 'homework')] ?? 'Ödev',
                'title' => (string) ($homework?->title ?: ($homework?->course?->name ?? 'Ödev #'.$homeworkId)),
                'status' => $progress->completed_at ? 'Tamamlandı' : 'Devam Ediyor',
                'xp' => (int) ($progress->xp_awarded ?? 0),
                'result' => $progress->reached_level ? 'Seviye '.$progress->reached_level : '-',
                'sort_date' => $progress->completed_at ?? $progress->started_at ?? $progress->created_at,
            ]);
        }

        $gameAssignmentsById = GameAssignment::withTrashed()
            ->whereIn('id', $gameProgress->keys())
            ->get()
            ->keyBy('id');
        foreach ($gameProgress as $assignmentId => $progress) {
            $assignment = $gameAssignmentsById->get((int) $assignmentId);
            $activityLog->push([
                'kind' => 'Oyun / Uygulama',
                'title' => trim((string) ($assignment?->game_name ?? '').' '.($assignment?->title ? '- '.$assignment->title : '')) ?: 'Oyun #'.$assignmentId,
                'status' => $progress->completed_at ? 'Tamamlandı' : ($progress->started_at ? 'Devam Ediyor' : 'Bekliyor'),
                'xp' => (int) ($progress->xp_awarded ?? 0),
                'result' => $progress->reached_level ? 'Seviye '.$progress->reached_level : '-',
                'sort_date' => $progress->completed_at ?? $progress->started_at ?? $progress->created_at,
            ]);
        }

        // Ders/odev/oyun disindaki diger icerik kayitlari (ContentProgress).
        foreach ($contentRows as $row) {
            $contentId = (string) $row->content_id;
            if (str_starts_with($contentId, 'course-') || str_starts_with($contentId, 'homework-') || str_starts_with($contentId, 'game-assignment-')) {
                continue;
            }
            $activityLog->push([
                'kind' => 'Etkinlik',
                'title' => (string) (data_get($row->payload, 'title') ?: data_get($row->payload, 'lesson_title') ?: $contentId),
                'status' => $row->completed ? 'Tamamlandı' : 'Devam Ediyor',
                'xp' => (int) ($row->xp_awarded ?? 0),
                'result' => '-',
                'sort_date' => $row->updated_at ?? $row->created_at,
            ]);
        }

        foreach ($liveItems as $item) {
            $activityLog->push($item);
        }

        foreach (RaceResult::query()->where('user_id', $student->user_id)->get() as $race) {
            $activityLog->push([
                'kind' => 'Klavye Yarışı',
                'title' => 'Klavye Yarışı',
                'status' => 'Katıldı',
                'xp' => (int) ($race->xp_earned ?? 0),
                'result' => ($race->wpm ?? 0).' WPM · %'.(int) round((float) ($race->accuracy ?? 0)),
                'sort_date' => $race->created_at,
            ]);
        }

        $activityLog = $activityLog
            ->sortByDesc(fn ($item) => $item['sort_date'] ? Carbon::parse($item['sort_date'])->timestamp : 0)
            ->values();

        $classAvgXp = $gradePeers->count() > 1
            ? (int) round($gradePeers->avg(fn ($peer) => (int) ($xpMap[$peer->id] ?? 0)))
            : 0;
        $analysis = $this->buildAnalysis($student, $activityLog, $categoryChart, $courseItems, [
            'total_xp' => (int) $totalXp,
            'overall_progress' => (int) $overallProgress,
            'completed_total' => (int) $completedTotal,
            'total_assignments' => (int) $totalAssignments,
            'class_rank' => $classRank,
            'class_size' => $gradePeers->count(),
            'class_avg_xp' => $classAvgXp,
            'quiz_joined' => (int) $quizJoinedCount,
            'competition_joined' => (int) $competitionJoinedCount,
        ]);
        return [
            'kpi' => [
                'total_xp' => $totalXp,
                'grade_avg' => $avgGrade,
                'quiz_joined_count' => $quizJoinedCount,
                'quiz_total_xp' => $quizXp,
                'competition_joined_count' => $competitionJoinedCount,
                'competition_total_xp' => $competitionXp + $keyboardRaceXp,
                'daily_attempt_count' => $dailyAttemptCount,
                'daily_correct_count' => $dailyCorrectCount,
                'daily_wrong_count' => $dailyWrongCount,
                'daily_full_correct_count' => $dailyFullCorrectCount,
                'daily_success_rate' => $dailySuccessRate,
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
            'live_items' => $liveItems,
            'activity_log' => $activityLog,
        ];
    }
    /**
     * Öğrenciye özel, veriye dayalı analiz maddeleri üretir.
     * Her madde: ['tone' => good|info|warn, 'title' => kısa başlık, 'text' => cümle].
     * Veri olmayan maddeler atlanır; en fazla 6 madde döner.
     *
     * @param  \Illuminate\Support\Collection  $activityLog
     * @param  iterable<int, array<string, mixed>>  $categoryChart
     * @param  iterable<int, array<string, mixed>>  $courseItems
     * @param  array<string, mixed>  $ctx
     * @return array<int, array{tone: string, title: string, text: string}>
     */
    private function buildAnalysis(Student $student, $activityLog, $categoryChart, $courseItems, array $ctx): array
    {
        $items = [];
        $first = trim((string) (explode(' ', trim((string) ($student->user?->name ?? '')))[0] ?? '')) ?: 'Öğrenci';
        $totalXp = (int) $ctx['total_xp'];

        // 1) Görev ilerlemesi
        $total = (int) $ctx['total_assignments'];
        if ($total > 0) {
            $done = (int) $ctx['completed_total'];
            $pct = (int) $ctx['overall_progress'];
            $pending = max(0, $total - $done);
            $items[] = [
                'tone' => $pct >= 75 ? 'good' : ($pct >= 40 ? 'info' : 'warn'),
                'title' => 'Görev ilerlemesi',
                'text' => "{$first}, atanan {$total} görevin {$done} tanesini (%{$pct}) tamamladı"
                    . ($pending > 0 ? "; {$pending} görev hâlâ bekliyor." : '. Bekleyen görevi yok.'),
            ];
        }

        // 2) Sınıf karşılaştırması
        $classSize = (int) $ctx['class_size'];
        $classAvg = (int) $ctx['class_avg_xp'];
        $gradeName = trim((string) ($student->schoolClass?->name ?? ''));
        $levelLabel = $gradeName !== '' ? "{$gradeName}. sınıf" : 'sınıf';
        if ($classSize > 1 && $classAvg > 0) {
            $ratio = $totalXp / $classAvg;
            if ($ratio >= 1.1) {
                $cmp = "{$levelLabel} ortalamasının (" . $classAvg . ' XP) ' . rtrim(rtrim(number_format($ratio, 1, ',', ''), '0'), ',') . ' katı';
            } elseif ($ratio >= 0.9) {
                $cmp = "{$levelLabel} ortalamasına (" . $classAvg . ' XP) yakın';
            } else {
                $cmp = "{$levelLabel} ortalamasının (" . $classAvg . ' XP) altında';
            }
            $rankText = $ctx['class_rank'] ? " {$gradeName}. sınıfta {$classSize} öğrenci arasında {$ctx['class_rank']}. sırada." : '';
            $items[] = [
                'tone' => ($ratio >= 1.1 || ($ctx['class_rank'] && $ctx['class_rank'] <= 3)) ? 'good' : ($ratio < 0.6 ? 'warn' : 'info'),
                'title' => 'Sınıf karşılaştırması',
                'text' => "{$totalXp} XP ile {$cmp}." . $rankText,
            ];
        }
        // 3) Güçlü / gelişime açık alan
        $cats = collect($categoryChart)->filter(fn ($c) => (int) ($c['total'] ?? 0) > 0)->values();
        if ($cats->isNotEmpty()) {
            $best = $cats->sortByDesc(fn ($c) => [(int) $c['value'], (int) $c['total']])->first();
            $weak = $cats->filter(fn ($c) => (int) $c['value'] < 100)->sortBy(fn ($c) => [(int) $c['value'], -(int) $c['total']])->first();
            $detail = fn (array $c) => isset($c['caption']) ? "{$c['caption']} katılım" : "%{$c['value']}, {$c['done']}/{$c['total']}";
            $text = "En güçlü alanı {$best['label']} ({$detail($best)}).";
            if ($weak && $weak['label'] !== $best['label']) {
                $text .= " Daha çok destek gereken alan {$weak['label']} ({$detail($weak)}).";
            } elseif (! $weak) {
                $text .= ' Görev aldığı tüm alanlarda tam tamamlama var.';
            }
            $items[] = ['tone' => $weak && (int) $weak['value'] < 40 ? 'warn' : 'info', 'title' => 'Güçlü ve gelişime açık alan', 'text' => $text];
        }

        // 4) Ders başarısı (doğru / yanlış)
        $correct = (int) collect($courseItems)->sum(fn ($i) => (int) ($i['correct_questions'] ?? 0));
        $wrong = (int) collect($courseItems)->sum(fn ($i) => (int) ($i['wrong_questions'] ?? 0));
        $answered = $correct + $wrong;
        if ($answered > 0) {
            $acc = (int) round($correct / $answered * 100);
            $note = $acc >= 85 ? 'Konu kavrayışı güçlü.' : ($acc >= 60 ? 'Yanlış yanıtlanan konuların tekrarı başarıyı daha da yükseltir.' : 'Ders slaytlarının tekrar edilmesi ve konu tekrarı önerilir.');
            $items[] = [
                'tone' => $acc >= 85 ? 'good' : ($acc >= 60 ? 'info' : 'warn'),
                'title' => 'Ders başarısı',
                'text' => "Derslerde {$answered} soru yanıtladı: {$correct} doğru, {$wrong} yanlış (doğruluk oranı %{$acc}). {$note}",
            ];
        }

        // 5) Çalışma düzeni ve devamlılık (Türkiye saatine göre)
        $tz = 'Europe/Istanbul';
        $stamps = collect($activityLog)
            ->map(fn ($row) => ! empty($row['sort_date']) ? Carbon::parse($row['sort_date'])->setTimezone($tz) : null)
            ->filter();
        if ($stamps->isNotEmpty()) {
            $parts = ['sabah (06-12)' => 0, 'öğleden sonra (12-18)' => 0, 'akşam (18-24)' => 0, 'gece (00-06)' => 0];
            $weekdays = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
            $dayCounts = [];
            foreach ($stamps as $t) {
                $h = (int) $t->format('G');
                $key = $h >= 6 && $h < 12 ? 'sabah (06-12)' : ($h >= 12 && $h < 18 ? 'öğleden sonra (12-18)' : ($h >= 18 ? 'akşam (18-24)' : 'gece (00-06)'));
                $parts[$key]++;
                $dayCounts[$t->dayOfWeekIso] = ($dayCounts[$t->dayOfWeekIso] ?? 0) + 1;
            }
            arsort($parts);
            arsort($dayCounts);
            $topPart = array_key_first($parts);
            $topPartPct = (int) round($parts[$topPart] / $stamps->count() * 100);
            $topDay = $weekdays[array_key_first($dayCounts)];

            $today = Carbon::now($tz)->startOfDay();
            $activeDates = $stamps->map(fn ($t) => $t->copy()->startOfDay()->toDateString())->unique()->values();
            $last30 = $activeDates->filter(fn ($d) => Carbon::parse($d, $tz)->gte($today->copy()->subDays(29)))->count();
            $streak = 0;
            $cursor = $today->copy();
            if (! $activeDates->contains($cursor->toDateString())) {
                $cursor->subDay();
            }
            while ($activeDates->contains($cursor->toDateString())) {
                $streak++;
                $cursor->subDay();
            }
            $lastActive = Carbon::parse($activeDates->sort()->last(), $tz);
            $daysAgo = (int) $lastActive->diffInDays($today);

            $text = "Son 30 günde {$last30} farklı günde çalıştı. " . ($topPartPct >= 100 ? 'Etkinliklerin tamamı' : 'Etkinliklerin çoğu') . " {$topPart} saatlerinde (%{$topPartPct}), en verimli gün {$topDay}.";
            $tone = 'info';
            if ($streak >= 2) {
                $text .= " Şu an {$streak} gündür kesintisiz çalışıyor.";
                $tone = 'good';
            } elseif ($daysAgo >= 7) {
                $text .= " Son etkinliği {$daysAgo} gün önceydi; düzenli ritim için kısa günlük görevlerle yeniden başlaması önerilir.";
                $tone = 'warn';
            } elseif ($daysAgo >= 1) {
                $text .= " Son etkinliği {$daysAgo} gün önceydi.";
            }
            $items[] = ['tone' => $tone, 'title' => 'Çalışma düzeni', 'text' => $text];
        }

        // 6) Sıradaki rozet hedefi
        $badgeCount = (int) $student->badges->count();
        $nextBadge = Badge::query()->where('xp_threshold', '>', $totalXp)->orderBy('xp_threshold')->first();
        if ($nextBadge) {
            $remaining = max(0, (int) $nextBadge->xp_threshold - $totalXp);
            $items[] = [
                'tone' => 'info',
                'title' => 'Sıradaki hedef',
                'text' => "{$badgeCount} rozet kazandı. “{$nextBadge->name}” rozetine {$remaining} XP kaldı.",
            ];
        } elseif ($badgeCount > 0) {
            $items[] = ['tone' => 'good', 'title' => 'Rozetler', 'text' => "XP rozet eşiklerinin tamamını aştı. Kazanılan rozet sayısı: {$badgeCount}."];
        }

        // 7) Yarışma / klavye (yeterli madde yoksa tamamlayıcı)
        if (count($items) < 6) {
            $bestWpm = 0;
            $bestAcc = 0;
            foreach ($activityLog as $row) {
                if (($row['kind'] ?? '') === 'Klavye Yarışı' && preg_match('/(\d+)\s*WPM\s*·\s*%(\d+)/u', (string) ($row['result'] ?? ''), $m) && (int) $m[1] > $bestWpm) {
                    $bestWpm = (int) $m[1];
                    $bestAcc = (int) $m[2];
                }
            }
            $joined = (int) $ctx['competition_joined'];
            $quiz = (int) $ctx['quiz_joined'];
            if ($bestWpm > 0 || $joined > 0 || $quiz > 0) {
                $parts = [];
                if ($bestWpm > 0) {
                    $parts[] = "klavye yarışında en iyi hızı {$bestWpm} WPM (%{$bestAcc} doğruluk)";
                }
                if ($joined > 0) {
                    $parts[] = "{$joined} canlı yarışmaya katıldı";
                }
                if ($quiz > 0) {
                    $parts[] = "{$quiz} canlı quize katıldı";
                }
                $items[] = ['tone' => 'info', 'title' => 'Yarışma ve etkileşim', 'text' => ucfirst(implode('; ', $parts)) . '.'];
            }
        }

        if ($items === []) {
            $items[] = ['tone' => 'info', 'title' => 'Henüz yeterli veri yok', 'text' => "{$first} için analiz oluşturacak etkinlik kaydı henüz bulunmuyor."];
        }

        return array_slice($items, 0, 6);
    }
}