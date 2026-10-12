<?php

namespace App\Services;

use App\Models\ContentProgress;
use App\Models\Course;
use App\Models\CourseHomework;
use App\Models\Grade;
use App\Models\LiveQuizAnswer;
use App\Models\LiveQuizQuestion;
use App\Models\LiveQuizSession;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentActivityLog;
use App\Models\StudentHomeworkProgress;
use App\Models\StudentTimeStat;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Öğrenci başarı analizi (sınıf bazlı istatistik + öğrenci detay sayfası) için tek hesap kaynağı.
 *
 * Başarı puanı, panodaki "Başarı Dağılımı" ile BİREBİR aynıdır:
 *   puan = (not ortalaması [varsa], ders tamamlama %, ödev tamamlama %) ortalaması;
 *   ders/ödev oranları sınıftaki en iyi öğrenciye göre ölçeklenir.
 * Eşikler: Çok İyi 60+, İyi 40-59, Orta 20-39, Düşük 0-19.
 *
 * Tarih aralığı verilirse tamamlama olayları (ders/ödev/quiz) o aralıkta sayılır; verilmezse tüm zamanlar
 * (panoyla aynı sonuç). Not ve yoklama bilinçli olarak kullanılmaz (not varsa puana panodaki gibi katılır).
 */
class StudentSuccessAnalytics
{
    public const BUCKETS = [
        ['key' => 'very_good', 'label' => 'Çok İyi (60+)', 'min' => 60, 'color' => '#16a34a'],
        ['key' => 'good', 'label' => 'İyi (40-59)', 'min' => 40, 'color' => '#2563eb'],
        ['key' => 'mid', 'label' => 'Orta (20-39)', 'min' => 20, 'color' => '#f59e0b'],
        ['key' => 'low', 'label' => 'Düşük (0-19)', 'min' => 0, 'color' => '#ef4444'],
    ];

    public function __construct(private StudentXpService $xpService)
    {
    }

    /** Admin tüm sınıfları, öğretmen yalnızca kendi sınıflarını görür. */
    public function allowedClassIds(?User $user): array
    {
        if (! $user) {
            return [];
        }
        if ($user->hasRole('admin')) {
            return SchoolClass::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        }
        $teacher = Teacher::query()->where('user_id', $user->id)->first();

        return $teacher
            ? $teacher->classes()->pluck('school_classes.id')->map(fn ($id) => (int) $id)->all()
            : [];
    }

    public function classCatalog(array $classIds): Collection
    {
        return SchoolClass::query()
            ->whereIn('id', $classIds)
            ->orderBy('name')->orderBy('section')
            ->get(['id', 'name', 'section', 'grade_level', 'academic_year']);
    }

    public function bucketFor(float $score): array
    {
        foreach (self::BUCKETS as $bucket) {
            if ($score >= $bucket['min']) {
                return $bucket;
            }
        }

        return self::BUCKETS[count(self::BUCKETS) - 1];
    }

    private function emptyResult(): array
    {
        return [
            'students' => [],
            'classes' => [],
            'overall' => $this->summarize([]),
            'series' => ['labels' => [], 'lessons' => [], 'homeworks' => [], 'quiz_correct' => [], 'quiz_wrong' => [], 'by_class' => []],
        ];
    }

    /**
     * @return array{students: array, classes: array, overall: array, series: array}
     */
    public function build(array $classIds, ?CarbonInterface $from = null, ?CarbonInterface $to = null, bool $withSeries = true): array
    {
        $classIds = array_values(array_unique(array_map('intval', $classIds)));
        if ($classIds === []) {
            return $this->emptyResult();
        }

        $students = Student::query()
            ->with(['user:id,name', 'schoolClass:id,name,section,grade_level'])
            ->whereIn('school_class_id', $classIds)
            ->get();
        if ($students->isEmpty()) {
            return $this->emptyResult();
        }

        $studentIds = $students->pluck('id')->all();
        $userIds = $students->pluck('user_id')->filter()->all();
        $inRange = function (?CarbonInterface $ts) use ($from, $to): bool {
            if (! $ts) {
                return false;
            }

            return (! $from || $ts->gte($from)) && (! $to || $ts->lte($to));
        };

        [$lessonIdsByClass, $homeworkIdsByClass] = $this->classContentIds($classIds);

        // --- Ders tamamlama ---
        $doneLessons = [];   // user_id => [content_id => true]
        $events = [];        // [ts, class_id, type, extra]
        $classByStudent = $students->pluck('school_class_id', 'id');
        $studentByUser = $students->pluck('id', 'user_id');
        foreach (ContentProgress::query()
            ->whereIn('user_id', $userIds)->where('completed', true)->where('content_id', 'like', 'course-%')
            ->get(['user_id', 'content_id', 'updated_at']) as $row) {
            if (! $inRange($row->updated_at)) {
                continue;
            }
            $doneLessons[$row->user_id][$row->content_id] = true;
            $sid = $studentByUser[$row->user_id] ?? null;
            $events[] = [$row->updated_at, (int) ($classByStudent[$sid] ?? 0), 'lesson', 0];
        }

        // --- Ödev tamamlama ---
        $doneHomeworks = []; // student_id => [homework_id => true]
        foreach (StudentHomeworkProgress::query()
            ->whereIn('student_id', $studentIds)->whereNotNull('completed_at')
            ->get(['student_id', 'course_homework_id', 'completed_at']) as $row) {
            if (! $inRange($row->completed_at)) {
                continue;
            }
            $doneHomeworks[$row->student_id][$row->course_homework_id] = true;
            $events[] = [$row->completed_at, (int) ($classByStudent[$row->student_id] ?? 0), 'homework', 0];
        }

        // --- Not ortalaması (varsa; panodaki gibi puana katılır) ---
        $gradeAvg = Grade::query()
            ->selectRaw('student_id, ROUND(AVG(score), 1) as avg_score')
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id')
            ->pluck('avg_score', 'student_id');

        // --- Canlı quiz ---
        $quizAnswered = [];
        $quizCorrect = [];
        $quizXp = [];
        foreach (LiveQuizAnswer::query()
            ->whereIn('student_user_id', $userIds)->whereNotNull('selected_answer')
            ->get(['student_user_id', 'is_correct', 'xp_earned', 'created_at']) as $row) {
            if (($from || $to) && ! $inRange($row->created_at)) {
                continue;
            }
            $uid = $row->student_user_id;
            $quizAnswered[$uid] = ($quizAnswered[$uid] ?? 0) + 1;
            $quizCorrect[$uid] = ($quizCorrect[$uid] ?? 0) + ($row->is_correct ? 1 : 0);
            $quizXp[$uid] = ($quizXp[$uid] ?? 0) + (int) $row->xp_earned;
            $sid = $studentByUser[$uid] ?? null;
            $events[] = [$row->created_at, (int) ($classByStudent[$sid] ?? 0), $row->is_correct ? 'quiz_correct' : 'quiz_wrong', 0];
        }

        $timeStats = StudentTimeStat::query()->whereIn('student_id', $studentIds)
            ->get(['student_id', 'total_seconds', 'last_seen_at'])->keyBy('student_id');
        $activeDays30 = StudentActivityLog::query()
            ->selectRaw('student_id, COUNT(DISTINCT DATE(logged_at)) as d')
            ->whereIn('student_id', $studentIds)->where('logged_at', '>=', now()->subDays(30))
            ->groupBy('student_id')->pluck('d', 'student_id');
        $totalXp = $this->xpService->earnedFor($students);

        // --- Oranlar ve sınıf içi ölçekleme ---
        $lessonRatio = [];
        $homeworkRatio = [];
        $lessonMax = [];
        $homeworkMax = [];
        $lessonCount = [];
        $homeworkCount = [];
        foreach ($students as $student) {
            $cid = (int) $student->school_class_id;
            $lessonIds = $lessonIdsByClass[$cid] ?? [];
            $homeworkIds = $homeworkIdsByClass[$cid] ?? [];
            $ld = count(array_intersect($lessonIds, array_keys($doneLessons[$student->user_id] ?? [])));
            $hd = count(array_intersect($homeworkIds, array_keys($doneHomeworks[$student->id] ?? [])));
            $lessonCount[$student->id] = [$ld, count($lessonIds)];
            $homeworkCount[$student->id] = [$hd, count($homeworkIds)];
            $lessonRatio[$student->id] = $lessonIds ? $ld / count($lessonIds) : null;
            $homeworkRatio[$student->id] = $homeworkIds ? $hd / count($homeworkIds) : null;
            $lessonMax[$cid] = max($lessonMax[$cid] ?? 0, $lessonRatio[$student->id] ?? 0);
            $homeworkMax[$cid] = max($homeworkMax[$cid] ?? 0, $homeworkRatio[$student->id] ?? 0);
        }

        $rows = [];
        $sevenDaysAgo = now()->subDays(7);
        foreach ($students as $student) {
            $cid = (int) $student->school_class_id;
            $signals = [];
            $grade = isset($gradeAvg[$student->id]) ? (float) $gradeAvg[$student->id] : null;
            if ($grade !== null) {
                $signals[] = $grade;
            }
            $lessonScaled = null;
            if ($lessonRatio[$student->id] !== null) {
                $lessonScaled = $lessonMax[$cid] > 0 ? ($lessonRatio[$student->id] / $lessonMax[$cid]) * 100 : 0.0;
                $signals[] = $lessonScaled;
            }
            $homeworkScaled = null;
            if ($homeworkRatio[$student->id] !== null) {
                $homeworkScaled = $homeworkMax[$cid] > 0 ? ($homeworkRatio[$student->id] / $homeworkMax[$cid]) * 100 : 0.0;
                $signals[] = $homeworkScaled;
            }
            $score = $signals ? array_sum($signals) / count($signals) : 0.0;
            $bucket = $this->bucketFor($score);
            $uid = $student->user_id;
            $answered = (int) ($quizAnswered[$uid] ?? 0);
            $correct = (int) ($quizCorrect[$uid] ?? 0);
            $stat = $timeStats->get($student->id);
            $lastSeen = $stat?->last_seen_at;

            $rows[(int) $student->id] = [
                'id' => (int) $student->id,
                'user_id' => (int) $uid,
                'name' => (string) ($student->user?->name ?? ('Öğrenci #'.$student->id)),
                'no' => (string) ($student->student_no ?? ''),
                'class_id' => $cid,
                'class_label' => trim(($student->schoolClass?->name ?? '').'/'.($student->schoolClass?->section ?? ''), '/'),
                'grade_level' => (int) ($student->schoolClass?->grade_level ?? 0),
                'lesson_done' => $lessonCount[$student->id][0],
                'lesson_total' => $lessonCount[$student->id][1],
                'homework_done' => $homeworkCount[$student->id][0],
                'homework_total' => $homeworkCount[$student->id][1],
                'lesson_pct' => $lessonRatio[$student->id] === null ? null : round($lessonRatio[$student->id] * 100, 1),
                'homework_pct' => $homeworkRatio[$student->id] === null ? null : round($homeworkRatio[$student->id] * 100, 1),
                'lesson_scaled' => $lessonScaled === null ? null : round($lessonScaled, 1),
                'homework_scaled' => $homeworkScaled === null ? null : round($homeworkScaled, 1),
                'grade_avg' => $grade,
                'score' => round($score, 1),
                'bucket' => $bucket['key'],
                'bucket_label' => $bucket['label'],
                'bucket_color' => $bucket['color'],
                'quiz_answered' => $answered,
                'quiz_correct' => $correct,
                'quiz_accuracy' => $answered > 0 ? round($correct / $answered * 100, 1) : null,
                'quiz_xp' => (int) ($quizXp[$uid] ?? 0),
                'total_xp' => (int) ($totalXp[$student->id] ?? 0),
                'active_seconds' => (int) ($stat?->total_seconds ?? 0),
                'last_seen_at' => $lastSeen?->toIso8601String(),
                'active_7d' => $lastSeen ? $lastSeen->gte($sevenDaysAgo) : false,
                'active_days_30' => (int) ($activeDays30[$student->id] ?? 0),
            ];
        }

        return [
            'students' => array_values($rows),
            'classes' => $this->classAggregates($rows),
            'overall' => $this->summarize($rows),
            'series' => $withSeries ? $this->weeklySeries($events, $classByStudent->unique()->all(), $students, $from, $to) : [],
        ];
    }

    /** [class_id => [lesson content ids], class_id => [homework ids]] (panodaki mantıkla aynı) */
    private function classContentIds(array $classIds): array
    {
        $lessonIdsByClass = [];
        $parentCourses = Course::query()->whereIn('school_class_id', $classIds)->get(['id', 'school_class_id']);
        $subCourses = Course::query()->whereIn('parent_course_id', $parentCourses->pluck('id'))->get(['id', 'parent_course_id']);
        foreach ($parentCourses as $c) {
            $lessonIdsByClass[$c->school_class_id][] = 'course-'.$c->id;
        }
        $classByParent = $parentCourses->pluck('school_class_id', 'id');
        foreach ($subCourses as $c) {
            $lessonIdsByClass[$classByParent[$c->parent_course_id]][] = 'course-'.$c->id;
        }
        $homeworkIdsByClass = CourseHomework::query()
            ->whereIn('school_class_id', $classIds)->get(['id', 'school_class_id'])
            ->groupBy('school_class_id')->map(fn ($r) => $r->pluck('id')->all())->all();

        return [$lessonIdsByClass, $homeworkIdsByClass];
    }

    private function avg(array $values): ?float
    {
        $values = array_values(array_filter($values, fn ($v) => $v !== null));

        return $values ? round(array_sum($values) / count($values), 1) : null;
    }

    private function summarize(array $rows): array
    {
        $count = count($rows);
        $buckets = [];
        foreach (self::BUCKETS as $b) {
            $buckets[$b['key']] = 0;
        }
        $answered = 0;
        $correct = 0;
        $active = 0;
        foreach ($rows as $r) {
            $buckets[$r['bucket']]++;
            $answered += $r['quiz_answered'];
            $correct += $r['quiz_correct'];
            $active += $r['active_7d'] ? 1 : 0;
        }

        return [
            'students' => $count,
            'avg_score' => $this->avg(array_column($rows, 'score')),
            'lesson_pct' => $this->avg(array_column($rows, 'lesson_pct')),
            'homework_pct' => $this->avg(array_column($rows, 'homework_pct')),
            'quiz_accuracy' => $answered > 0 ? round($correct / $answered * 100, 1) : null,
            'quiz_answered' => $answered,
            'active_pct' => $count > 0 ? round($active / $count * 100, 1) : null,
            'risk' => $buckets['low'],
            'buckets' => $buckets,
        ];
    }

    private function classAggregates(array $rows): array
    {
        $byClass = [];
        foreach ($rows as $r) {
            $byClass[$r['class_id']][] = $r;
        }
        $out = [];
        foreach ($byClass as $cid => $classRows) {
            $summary = $this->summarize($classRows);
            $out[] = ['id' => $cid, 'label' => $classRows[0]['class_label'], 'grade_level' => $classRows[0]['grade_level']] + $summary;
        }
        usort($out, fn ($a, $b) => strnatcasecmp($a['label'], $b['label']));

        return $out;
    }

    /** Haftalık etkinlik serisi (ders/ödev/quiz). Aralık yoksa son 12 hafta. */
    private function weeklySeries(array $events, array $classIds, Collection $students, ?CarbonInterface $from, ?CarbonInterface $to): array
    {
        $end = ($to ? Carbon::instance($to) : now())->copy()->startOfWeek();
        $start = $from ? Carbon::instance($from)->copy()->startOfWeek() : $end->copy()->subWeeks(11);
        if ($start->diffInWeeks($end) > 25) {
            $start = $end->copy()->subWeeks(25);
        }
        $weeks = [];
        for ($w = $start->copy(); $w->lte($end); $w->addWeek()) {
            $weeks[$w->format('Y-m-d')] = count($weeks);
        }
        $n = count($weeks);
        $zero = array_fill(0, $n, 0);
        $series = ['labels' => array_map(fn ($k) => Carbon::parse($k)->format('d.m'), array_keys($weeks)),
            'lessons' => $zero, 'homeworks' => $zero, 'quiz_correct' => $zero, 'quiz_wrong' => $zero, 'by_class' => []];
        $classLabels = $students->mapWithKeys(fn ($s) => [(int) $s->school_class_id => trim(($s->schoolClass?->name ?? '').'/'.($s->schoolClass?->section ?? ''), '/')])->all();
        foreach ($events as [$ts, $cid, $type]) {
            $key = Carbon::instance($ts)->startOfWeek()->format('Y-m-d');
            if (! isset($weeks[$key])) {
                continue;
            }
            $i = $weeks[$key];
            $field = match ($type) {
                'lesson' => 'lessons', 'homework' => 'homeworks', 'quiz_correct' => 'quiz_correct', default => 'quiz_wrong',
            };
            $series[$field][$i]++;
            if (! isset($series['by_class'][$cid])) {
                $series['by_class'][$cid] = ['label' => $classLabels[$cid] ?? ('Sınıf '.$cid), 'data' => $zero, 'total' => 0];
            }
            $series['by_class'][$cid]['data'][$i]++;
            $series['by_class'][$cid]['total']++;
        }
        uasort($series['by_class'], fn ($a, $b) => $b['total'] <=> $a['total']);
        $series['by_class'] = array_slice($series['by_class'], 0, 8, true);

        return $series;
    }

    // =====================================================================
    //  Öğrenci detayı
    // =====================================================================

    public function studentDetail(Student $student, ?CarbonInterface $from, ?CarbonInterface $to, string $type = 'all'): array
    {
        $student->loadMissing(['user:id,name', 'schoolClass:id,name,section,grade_level']);
        $classId = (int) $student->school_class_id;
        $data = $this->build([$classId], $from, $to, false);

        $row = collect($data['students'])->firstWhere('id', (int) $student->id) ?? [];
        $classRows = collect($data['students'])->sortByDesc('score')->values();
        $rank = $classRows->search(fn ($r) => (int) $r['id'] === (int) $student->id);
        $rank = $rank === false ? null : $rank + 1;
        $classSize = $classRows->count();
        $classAgg = $data['classes'][0] ?? $this->summarize([]);
        $percentile = ($rank !== null && $classSize > 1) ? (int) round((1 - ($rank - 1) / ($classSize - 1)) * 100) : null;

        $userId = (int) $student->user_id;
        $inRange = function (?CarbonInterface $ts) use ($from, $to): bool {
            if (! $ts) {
                return false;
            }

            return (! $from || $ts->gte($from)) && (! $to || $ts->lte($to));
        };

        // Dersler
        [$lessonIdsByClass, $homeworkIdsByClass] = $this->classContentIds([$classId]);
        $lessonContentIds = $lessonIdsByClass[$classId] ?? [];
        $courseIds = array_map(fn ($id) => (int) str_replace('course-', '', $id), $lessonContentIds);
        $courseNames = Course::query()->whereIn('id', $courseIds)->pluck('name', 'id');
        $progress = ContentProgress::query()
            ->where('user_id', $userId)->whereIn('content_id', $lessonContentIds)
            ->get()->keyBy('content_id');
        $lessons = [];
        foreach ($lessonContentIds as $cid) {
            $p = $progress->get($cid);
            $done = $p && $p->completed && $inRange($p->updated_at);
            $payload = (array) ($p?->payload ?? []);
            $qTotal = (int) ($payload['question_total'] ?? 0);
            $qCorrect = (int) ($payload['correct_questions'] ?? 0);
            $lessons[] = [
                'name' => (string) ($courseNames[(int) str_replace('course-', '', $cid)] ?? $cid),
                'done' => (bool) $done,
                'done_at' => $done ? $p->updated_at->format('d.m.Y') : null,
                'xp' => $done ? (int) $p->xp_awarded : 0,
                'q_total' => $qTotal,
                'q_correct' => $qCorrect,
                'q_wrong' => (int) ($payload['wrong_questions'] ?? max(0, $qTotal - $qCorrect)),
                'q_pct' => $done && $qTotal > 0 ? round($qCorrect / $qTotal * 100) : null,
                'duration' => $done ? (int) ($payload['duration_seconds'] ?? 0) : 0,
            ];
        }
        $scored = array_values(array_filter($lessons, fn ($l) => $l['q_pct'] !== null));
        usort($scored, fn ($a, $b) => $b['q_pct'] <=> $a['q_pct']);
        $strong = array_slice($scored, 0, 3);
        $weak = array_slice(array_reverse($scored), 0, 3);

        // Ödevler
        $homeworks = [];
        $hwIds = $homeworkIdsByClass[$classId] ?? [];
        $hwProgress = StudentHomeworkProgress::query()->where('student_id', $student->id)->whereIn('course_homework_id', $hwIds)->get()->keyBy('course_homework_id');
        foreach (CourseHomework::query()->whereIn('id', $hwIds)->orderByDesc('id')->get(['id', 'title', 'assignment_type', 'due_date']) as $hw) {
            $p = $hwProgress->get($hw->id);
            $done = $p && $p->completed_at && $inRange($p->completed_at);
            $homeworks[] = [
                'title' => (string) $hw->title,
                'type' => (string) $hw->assignment_type,
                'due' => $hw->due_date ? Carbon::parse($hw->due_date)->format('d.m.Y') : null,
                'done' => (bool) $done,
                'done_at' => $done ? $p->completed_at->format('d.m.Y') : null,
                'xp' => $done ? (int) $p->xp_awarded : 0,
                'seconds' => $done ? (int) $p->completion_seconds : 0,
            ];
        }

        // Quiz
        $answers = LiveQuizAnswer::query()
            ->where('student_user_id', $userId)->whereNotNull('selected_answer')
            ->get(['live_quiz_session_id', 'question_index', 'is_correct', 'xp_earned', 'created_at'])
            ->filter(fn ($a) => (! $from && ! $to) || $inRange($a->created_at));
        $bySession = $answers->groupBy('live_quiz_session_id');
        $sessions = LiveQuizSession::query()->with('quiz:id,title')
            ->whereIn('id', $bySession->keys())->orderByDesc('id')->limit(10)->get();
        $totals = LiveQuizAnswer::query()
            ->selectRaw('live_quiz_session_id, student_user_id, SUM(xp_earned) as xp')
            ->whereIn('live_quiz_session_id', $sessions->pluck('id'))
            ->groupBy('live_quiz_session_id', 'student_user_id')->get()->groupBy('live_quiz_session_id');
        $quizzes = [];
        foreach ($sessions as $s) {
            $mine = $bySession->get($s->id, collect());
            $xpList = $totals->get($s->id, collect())->sortByDesc('xp')->values();
            $pos = $xpList->search(fn ($t) => (int) $t->student_user_id === $userId);
            $quizzes[] = [
                'title' => (string) ($s->quiz?->title ?? 'Canlı Quiz'),
                'date' => $s->created_at?->format('d.m.Y'),
                'answered' => $mine->count(),
                'correct' => $mine->where('is_correct', true)->count(),
                'xp' => (int) $mine->sum('xp_earned'),
                'rank' => $pos === false ? null : $pos + 1,
                'participants' => $xpList->count(),
            ];
        }
        $wrongList = [];
        $wrongAnswers = $answers->where('is_correct', false)->sortByDesc('created_at')->take(10);
        if ($wrongAnswers->isNotEmpty()) {
            $sessMap = LiveQuizSession::query()->with('quiz:id,title')->whereIn('id', $wrongAnswers->pluck('live_quiz_session_id')->unique())->get()->keyBy('id');
            $quizIds = $sessMap->pluck('live_quiz_id')->unique();
            $qMap = LiveQuizQuestion::query()->whereIn('live_quiz_id', $quizIds)->get(['live_quiz_id', 'sort_order', 'question_text'])
                ->mapWithKeys(fn ($q) => [$q->live_quiz_id.'-'.$q->sort_order => $q->question_text]);
            foreach ($wrongAnswers as $a) {
                $sess = $sessMap->get($a->live_quiz_session_id);
                $wrongList[] = [
                    'quiz' => (string) ($sess?->quiz?->title ?? 'Canlı Quiz'),
                    'question' => (string) ($qMap[($sess?->live_quiz_id ?? 0).'-'.$a->question_index] ?? ('Soru '.($a->question_index + 1))),
                    'date' => $a->created_at?->format('d.m.Y'),
                ];
            }
        }

        // Haftalık seri (öğrenciye özel, XP dahil)
        $series = $this->studentWeeklySeries($student, $answers, $progress, $hwProgress, $from, $to, $type);

        // Radar (öğrenci - sınıf ortalaması)
        $avg = fn (string $k) => $this->avg(array_column($data['students'], $k)) ?? 0;
        $radar = [
            'labels' => ['Ders', 'Ödev', 'Quiz Doğruluğu', 'Katılım (30 gün)'],
            'student' => [$row['lesson_scaled'] ?? 0, $row['homework_scaled'] ?? 0, $row['quiz_accuracy'] ?? 0, round(min(30, (int) ($row['active_days_30'] ?? 0)) / 30 * 100, 1)],
            'class' => [$avg('lesson_scaled'), $avg('homework_scaled'), $avg('quiz_accuracy'), round(min(30, $this->avg(array_column($data['students'], 'active_days_30')) ?? 0) / 30 * 100, 1)],
        ];

        // Son etkinlikler
        $timeline = [];
        foreach ($lessons as $l) {
            if ($l['done']) {
                $timeline[] = ['at' => $l['done_at'], 'sort' => $l['done_at'], 'icon' => '📘', 'text' => 'Dersi bitirdi: '.$l['name'].($l['xp'] ? ' (+'.$l['xp'].' XP)' : '')];
            }
        }
        foreach ($homeworks as $h) {
            if ($h['done']) {
                $timeline[] = ['at' => $h['done_at'], 'sort' => $h['done_at'], 'icon' => '📝', 'text' => 'Ödev teslim etti: '.$h['title']];
            }
        }
        foreach ($quizzes as $q) {
            $timeline[] = ['at' => $q['date'], 'sort' => $q['date'], 'icon' => '🎯', 'text' => 'Quiz: '.$q['title'].' — '.$q['correct'].'/'.$q['answered'].' doğru'.($q['rank'] ? ', '.$q['rank'].'. sıra' : '')];
        }
        usort($timeline, fn ($a, $b) => Carbon::createFromFormat('d.m.Y', $b['sort'])->timestamp <=> Carbon::createFromFormat('d.m.Y', $a['sort'])->timestamp);
        $timeline = array_slice($timeline, 0, 15);

        return [
            'row' => $row,
            'rank' => $rank,
            'class_size' => $classSize,
            'percentile' => $percentile,
            'class' => $classAgg,
            'radar' => $radar,
            'lessons' => $lessons,
            'strong' => $strong,
            'weak' => $weak,
            'homeworks' => $homeworks,
            'quizzes' => $quizzes,
            'wrong_questions' => $wrongList,
            'series' => $series,
            'timeline' => $timeline,
            'type' => $type,
        ];
    }

    private function studentWeeklySeries(Student $student, Collection $answers, Collection $progress, Collection $hwProgress, ?CarbonInterface $from, ?CarbonInterface $to, string $type): array
    {
        $end = ($to ? Carbon::instance($to) : now())->copy()->startOfWeek();
        $start = $from ? Carbon::instance($from)->copy()->startOfWeek() : $end->copy()->subWeeks(11);
        if ($start->diffInWeeks($end) > 25) {
            $start = $end->copy()->subWeeks(25);
        }
        $weeks = [];
        for ($w = $start->copy(); $w->lte($end); $w->addWeek()) {
            $weeks[$w->format('Y-m-d')] = count($weeks);
        }
        $zero = array_fill(0, count($weeks), 0);
        $s = ['labels' => array_map(fn ($k) => Carbon::parse($k)->format('d.m'), array_keys($weeks)),
            'lessons' => $zero, 'homeworks' => $zero, 'quiz_correct' => $zero, 'quiz_wrong' => $zero, 'xp' => $zero];
        $put = function (?CarbonInterface $ts, string $field, int $xp = 0) use (&$s, $weeks) {
            if (! $ts) {
                return;
            }
            $k = Carbon::instance($ts)->startOfWeek()->format('Y-m-d');
            if (! isset($weeks[$k])) {
                return;
            }
            $s[$field][$weeks[$k]]++;
            $s['xp'][$weeks[$k]] += $xp;
        };
        $want = fn (string $t) => $type === 'all' || $type === $t;
        if ($want('lesson')) {
            foreach ($progress as $p) {
                if ($p->completed) {
                    $put($p->updated_at, 'lessons', (int) $p->xp_awarded);
                }
            }
        }
        if ($want('homework')) {
            foreach ($hwProgress as $p) {
                if ($p->completed_at) {
                    $put($p->completed_at, 'homeworks', (int) $p->xp_awarded);
                }
            }
        }
        if ($want('quiz')) {
            foreach ($answers as $a) {
                $put($a->created_at, $a->is_correct ? 'quiz_correct' : 'quiz_wrong', (int) $a->xp_earned);
            }
        }

        return $s;
    }
}
