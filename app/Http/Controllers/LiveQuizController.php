<?php

namespace App\Http\Controllers;

use App\Models\LiveQuiz;
use App\Models\LiveQuizAnswer;
use App\Models\LiveQuizParticipant;
use App\Models\LiveQuizQuestion;
use App\Models\LiveQuizSession;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentReport;
use App\Models\UserProfile;
use App\Support\Utf8Text;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LiveQuizController extends Controller
{
    public function index()
    {
        $teacherId = auth()->id();
        $classes = SchoolClass::query()->orderBy('name')->orderBy('section')->get();
        $quizzes = LiveQuiz::query()
            ->withCount('questions')
            ->where('teacher_user_id', $teacherId)
            ->where('status', '!=', 'archived')
            ->latest()
            ->get();
        $sessions = LiveQuizSession::query()
            ->with('quiz')
            ->where('teacher_user_id', $teacherId)
            ->latest()
            ->limit(25)
            ->get();

        return view('live-quiz.index', compact('classes', 'quizzes', 'sessions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'join_mode' => ['required', 'in:code,instant'],
            'questions_json' => ['required', 'string'],
        ]);

        $questions = json_decode($data['questions_json'], true);
        if (!is_array($questions) || count($questions) < 1) {
            return back()->withErrors(['questions_json' => 'En az 1 soru gerekli.'])->withInput();
        }

        DB::transaction(function () use ($data, $questions) {
            $quiz = LiveQuiz::query()->create([
                'teacher_user_id' => auth()->id(),
                'title' => $data['title'],
                'school_class_id' => $data['school_class_id'] ?: null,
                'join_mode' => (string) ($data['join_mode'] ?? 'code'),
                'status' => 'active',
            ]);

            foreach (array_values($questions) as $i => $rawQuestion) {
                $normalized = $this->normalizeQuestion($rawQuestion);

                LiveQuizQuestion::query()->create([
                    'live_quiz_id' => $quiz->id,
                    'sort_order' => $i,
                    'type' => $normalized['type'],
                    'question_text' => $normalized['question_text'],
                    'options' => $normalized['options'],
                    'correct_answer' => $normalized['correct_answer'],
                    'duration_sec' => $normalized['duration_sec'],
                    'xp' => $normalized['xp'],
                    'double_xp' => $normalized['double_xp'],
                ]);
            }
        });

        return redirect()->route('live-quiz.index')->with('ok', 'Quiz kaydedildi.');
    }

    public function start(LiveQuiz $quiz)
    {
        abort_unless($quiz->teacher_user_id === auth()->id(), 403);
        abort_unless($quiz->questions()->exists(), 422);

        // Once soru sayaci baslatilmiyor: ogrenciler once bir "lobi" ekraninda
        // katilim koduyla toplanir, ogretmen "Herkese Baslat" dedigi an ayni
        // milisaniyede birinci sorunun suresi herkes icin birlikte baslar.
        $session = LiveQuizSession::query()->create([
            'live_quiz_id' => $quiz->id,
            'teacher_user_id' => auth()->id(),
            'join_code' => strtoupper(Str::random(6)),
            'status' => 'lobby',
            'current_index' => 0,
            'is_locked' => true,
            'started_at_ms' => null,
            'ends_at_ms' => null,
        ]);

        return redirect()->route('live-quiz.session.show', $session)->with('ok', 'Lobi olusturuldu. Ogrenciler koda katilinca "Herkese Baslat" butonuna basin.');
    }

    public function launch(LiveQuizSession $session)
    {
        abort_unless($session->teacher_user_id === auth()->id(), 403);
        if ($session->status !== 'lobby') {
            return back();
        }

        $first = $session->quiz()->first()?->questions()->orderBy('sort_order')->first();
        $duration = max(5, (int) ($first?->duration_sec ?? 30));
        $nowMs = $this->nowMs();

        $session->update([
            'status' => 'live',
            'current_index' => 0,
            'is_locked' => false,
            'started_at_ms' => $nowMs,
            'ends_at_ms' => $nowMs + ($duration * 1000),
        ]);

        return back()->with('ok', 'Quiz herkes icin ayni anda baslatildi.');
    }

    public function showSession(LiveQuizSession $session)
    {
        abort_unless($session->teacher_user_id === auth()->id(), 403);
        $session = $this->syncSessionByTimer($session);
        if ($session->status === 'finished') {
            return redirect()->route('live-quiz.session.report', $session)->with('ok', 'Quiz tamamlandi. Rapor asagida.');
        }
        $session->load(['quiz.questions', 'participants.studentUser']);
        $rows = $this->leaderboardRows($session);
        $currentQuestionStats = $this->currentQuestionStats($session);

        return view('live-quiz.session', compact('session', 'rows', 'currentQuestionStats'));
    }

    /**
     * Ogretmen/admin ekraninin AJAX ile (sayfa yenilemeden) canli sayacini,
     * katilan/cevaplayan sayilarini ve durumunu guncellemesi icin JSON uc noktasi.
     */
    public function sessionStatus(LiveQuizSession $session)
    {
        abort_unless($session->teacher_user_id === auth()->id(), 403);
        $session = $this->syncSessionByTimer($session);
        $session->load('quiz.questions');
        $questions = $session->quiz?->questions?->sortBy('sort_order')->values() ?? collect();
        $current = $questions->get((int) $session->current_index);

        return response()->json([
            'status' => $session->status,
            'current_index' => (int) $session->current_index,
            'question_count' => $questions->count(),
            'question_text' => $current?->question_text,
            'is_locked' => (bool) $session->is_locked,
            'ends_at_ms' => (int) ($session->ends_at_ms ?? 0),
            'server_now_ms' => $this->nowMs(),
            'stats' => $this->currentQuestionStats($session),
            'rows' => $this->leaderboardRows($session),
            'report_url' => route('live-quiz.session.report', $session),
        ]);
    }

    public function sessionReport(LiveQuizSession $session)
    {
        abort_unless($session->teacher_user_id === auth()->id(), 403);
        $session->load(['quiz.questions', 'participants.studentUser']);

        $questions = $session->quiz?->questions?->sortBy('sort_order')->values() ?? collect();
        $answers = LiveQuizAnswer::query()
            ->where('live_quiz_session_id', $session->id)
            ->get()
            ->groupBy('question_index');

        $questionBreakdown = $questions->map(function ($question, $index) use ($answers) {
            $qAnswers = $answers->get($index, collect());
            $answered = $qAnswers->count();
            $correct = $qAnswers->where('is_correct', true)->count();
            $avgAnsweredAtMs = $answered > 0 ? (int) round($qAnswers->avg('answered_at_ms')) : null;

            return [
                'index' => $index,
                'question_text' => $question->question_text,
                'answered' => $answered,
                'correct' => $correct,
                'wrong' => max(0, $answered - $correct),
                'avg_xp' => $answered > 0 ? round($qAnswers->avg('xp_earned'), 1) : 0,
            ];
        })->values()->all();

        $rows = $this->leaderboardRows($session);

        return view('live-quiz.report', compact('session', 'questionBreakdown', 'rows'));
    }

    public function next(LiveQuizSession $session)
    {
        abort_unless($session->teacher_user_id === auth()->id(), 403);
        $session = $this->syncSessionByTimer($session);
        if ($session->status !== 'live') {
            return redirect()->route('live-quiz.index')->with('ok', 'Quiz tamamlandi. Quiz listesine yonlendirildiniz.');
        }

        $questions = $session->quiz->questions()->sortBy('sort_order')->values();
        $next = (int) $session->current_index + 1;
        if ($next >= $questions->count()) {
            return $this->finish($session);
        }

        $duration = max(5, (int) ($questions[$next]->duration_sec ?? 30));
        $session->update([
            'current_index' => $next,
            'is_locked' => false,
            'ends_at_ms' => $this->nowMs() + ($duration * 1000),
        ]);

        return back()->with('ok', 'Sonraki soruya gecildi.');
    }

    public function toggleLock(LiveQuizSession $session)
    {
        abort_unless($session->teacher_user_id === auth()->id(), 403);
        $session->update(['is_locked' => !$session->is_locked]);

        return back()->with('ok', $session->is_locked ? 'Soru kilitlendi.' : 'Soru acildi.');
    }

    public function finish(LiveQuizSession $session)
    {
        abort_unless($session->teacher_user_id === auth()->id(), 403);

        if ($session->status !== 'finished') {
            $session->update([
                'status' => 'finished',
                'is_locked' => true,
                'finished_at_ms' => $this->nowMs(),
            ]);
            $this->writeQuizToStudentReports($session->fresh('quiz'));
        }

        return redirect()->route('live-quiz.index')->with('ok', 'Quiz tamamlandi ve quiz listesine yonlendirildiniz.');
    }

    public function studentJoinForm()
    {
        return view('student-portal.live-quiz-join');
    }

    public function studentJoin(Request $request)
    {
        $data = $request->validate(['join_code' => ['required', 'string', 'size:6']]);
        $session = LiveQuizSession::query()
            ->where('join_code', Str::upper($data['join_code']))
            ->whereIn('status', ['lobby', 'live'])
            ->first();
        if (!$session) {
            return back()->withErrors(['join_code' => 'Aktif oturum bulunamadi.']);
        }

        $session = $this->syncSessionByTimer($session);
        if (!in_array($session->status, ['lobby', 'live'], true)) {
            return back()->withErrors(['join_code' => 'Bu oturumun suresi doldu veya quiz bitti.']);
        }
        if (!$this->studentCanJoinSession($session, auth()->id())) {
            return back()->withErrors(['join_code' => 'Bu quiz senin sinifina acik degil.']);
        }

        LiveQuizParticipant::query()->updateOrCreate(
            [
                'live_quiz_session_id' => $session->id,
                'student_user_id' => auth()->id(),
            ],
            [
                'joined_at_ms' => $this->nowMs(),
            ]
        );

        return redirect()->route('student.live-quiz.play', $session);
    }

    public function studentInstantJoin(LiveQuizSession $session)
    {
        abort_unless(auth()->user()?->hasRole('student'), 403);
        $session = $this->syncSessionByTimer($session);
        abort_unless(in_array($session->status, ['lobby', 'live'], true), 403);
        abort_unless(((string) ($session->quiz?->join_mode ?? 'code')) === 'instant', 403);
        abort_unless($this->studentCanJoinSession($session, auth()->id()), 403);

        LiveQuizParticipant::query()->updateOrCreate(
            [
                'live_quiz_session_id' => $session->id,
                'student_user_id' => auth()->id(),
            ],
            [
                'joined_at_ms' => $this->nowMs(),
            ]
        );

        return redirect()->route('student.live-quiz.play', $session);
    }

    public function studentPlay(LiveQuizSession $session)
    {
        abort_unless(auth()->user()?->hasRole('student'), 403);
        $session = $this->syncSessionByTimer($session);
        abort_unless(in_array($session->status, ['lobby', 'live', 'finished'], true), 403);
        abort_unless($this->studentCanJoinSession($session, auth()->id()), 403);
        if (!LiveQuizParticipant::query()
            ->where('live_quiz_session_id', $session->id)
            ->where('student_user_id', auth()->id())
            ->exists()) {
            abort(403);
        }
        if ($session->status === 'finished') {
            return redirect()->route('student.portal.dashboard')->with('ok', 'Quizi tamamladin. Anasayfaya yonlendiriliyorsun.');
        }
        $session->load('quiz.questions');
        $alreadyAnsweredCurrent = LiveQuizAnswer::query()
            ->where('live_quiz_session_id', $session->id)
            ->where('student_user_id', auth()->id())
            ->where('question_index', (int) $session->current_index)
            ->exists();
        $joinedCount = $session->status === 'lobby' ? $session->participants()->count() : 0;

        return view('student-portal.live-quiz-play', compact('session', 'alreadyAnsweredCurrent', 'joinedCount'));
    }

    /**
     * Ogrenci ekraninin lobide "ogretmen baslatti mi" ve sorudayken sunucu
     * saatiyle senkron kalan sureyi sayfa yenilemeden kontrol etmesi icin.
     */
    public function studentSessionStatus(LiveQuizSession $session)
    {
        abort_unless(auth()->user()?->hasRole('student'), 403);
        $session = $this->syncSessionByTimer($session);
        abort_unless($this->studentCanJoinSession($session, auth()->id()), 403);

        return response()->json([
            'status' => $session->status,
            'current_index' => (int) $session->current_index,
            'is_locked' => (bool) $session->is_locked,
            'ends_at_ms' => (int) ($session->ends_at_ms ?? 0),
            'server_now_ms' => $this->nowMs(),
            'joined_count' => $session->status === 'lobby' ? $session->participants()->count() : null,
        ]);
    }

    public function studentAnswer(Request $request, LiveQuizSession $session)
    {
        abort_unless(auth()->user()?->hasRole('student'), 403);
        $session = $this->syncSessionByTimer($session);
        abort_unless($this->studentCanJoinSession($session, auth()->id()), 403);
        if ($session->status !== 'live' || $session->is_locked) {
            if ($session->status === 'finished') {
                return redirect()->route('student.portal.dashboard')->with('ok', 'Quizi tamamladin. Anasayfaya yonlendiriliyorsun.');
            }
            return back()->withErrors(['answer' => 'Bu soru su an kilitli veya oturum bitmis.']);
        }

        $questionIndex = (int) $request->input('question_index', -1);
        if ($questionIndex !== (int) $session->current_index) {
            return back()->withErrors(['answer' => 'Bu soru artik aktif degil.']);
        }

        $question = $session->quiz->questions()->orderBy('sort_order')->skip($questionIndex)->first();
        if (!$question) {
            return back();
        }

        $studentUserId = auth()->id();
        $exists = LiveQuizAnswer::query()
            ->where('live_quiz_session_id', $session->id)
            ->where('student_user_id', $studentUserId)
            ->where('question_index', $questionIndex)
            ->exists();
        if ($exists) {
            return redirect()->route('student.live-quiz.play', $session);
        }

        $evaluation = $this->evaluateAnswer($request, $question);
        $answeredAtMs = $this->nowMs();
        $xp = $evaluation['is_correct'] ? $this->calculateSpeedXp($session, $question, $answeredAtMs) : 0;

        LiveQuizAnswer::query()->create([
            'live_quiz_session_id' => $session->id,
            'student_user_id' => $studentUserId,
            'question_index' => $questionIndex,
            'selected_answer' => $evaluation['selected_answer'],
            'is_correct' => $evaluation['is_correct'],
            'xp_earned' => $xp,
            'answered_at_ms' => $answeredAtMs,
        ]);

        if ($xp > 0) {
            UserProfile::query()->where('user_id', $studentUserId)->increment('xp', $xp);
        }

        $rank = $this->studentRankInSession($session, $studentUserId);
        $sessionXp = (int) LiveQuizAnswer::query()
            ->where('live_quiz_session_id', $session->id)
            ->where('student_user_id', $studentUserId)
            ->sum('xp_earned');
        $correctAnswerText = $this->resolveCorrectAnswerText($question);
        $studentAnswerText = $this->resolveSelectedAnswerText($question, (string) ($evaluation['selected_answer'] ?? ''));

        return redirect()->route('student.live-quiz.play', $session)->with('answer_feedback', [
            'is_correct' => $evaluation['is_correct'],
            'xp' => $xp,
            'session_total_xp' => $sessionXp,
            'rank' => $rank['rank'],
            'total' => $rank['total'],
            'question_index' => $questionIndex,
            'correct_answer_text' => $correctAnswerText,
            'student_answer_text' => $studentAnswerText,
        ]);
    }

    public function studentActiveSession()
    {
        if (! auth()->user()?->hasRole('student')) {
            return response()->json(['active' => false]);
        }

        $session = LiveQuizSession::query()
            ->with('quiz')
            ->whereIn('status', ['lobby', 'live'])
            ->latest('id')
            ->get()
            ->first(function (LiveQuizSession $candidate) {
                return ((string) ($candidate->quiz?->join_mode ?? 'code')) === 'instant'
                    && $this->studentCanJoinSession($candidate, auth()->id());
            });

        if (!$session) {
            return response()->json(['active' => false]);
        }

        $session = $this->syncSessionByTimer($session);
        if (!in_array($session->status, ['lobby', 'live'], true) || ((string) ($session->quiz?->join_mode ?? 'code')) !== 'instant') {
            return response()->json(['active' => false]);
        }

        $joined = LiveQuizParticipant::query()
            ->where('live_quiz_session_id', $session->id)
            ->where('student_user_id', auth()->id())
            ->exists();

        return response()->json([
            'active' => true,
            'session_id' => $session->id,
            'quiz_title' => (string) ($session->quiz?->title ?? 'Canli Quiz'),
            'join_url' => route('student.live-quiz.instant-join', $session),
            'joined' => $joined,
        ]);
    }

    private function normalizeQuestion(array $raw): array
    {
        $type = Str::lower((string) ($raw['type'] ?? 'multiple'));
        if (!in_array($type, ['multiple', 'truefalse', 'dragdrop'], true)) {
            $type = 'multiple';
        }

        $questionText = trim($this->forceUtf8((string) ($raw['question'] ?? '')));
        if ($questionText === '') {
            $questionText = 'Yeni soru';
        }

        $durationSec = max(5, (int) ($raw['durationSec'] ?? 30));
        $xp = max(1, (int) ($raw['xp'] ?? 10));
        $doubleXp = !empty($raw['doubleXp']);

        if ($type === 'truefalse') {
            $correctValue = Str::upper((string) ($raw['correct'] ?? 'A'));
            $correctValue = in_array($correctValue, ['A', 'B'], true) ? $correctValue : 'A';

            return [
                'type' => 'truefalse',
                'question_text' => $questionText,
                'options' => ['Dogru', 'Yanlis'],
                'correct_answer' => $correctValue,
                'duration_sec' => $durationSec,
                'xp' => $xp,
                'double_xp' => $doubleXp,
            ];
        }

        if ($type === 'dragdrop') {
            $left = array_values(array_filter(array_map(fn ($v) => trim($this->forceUtf8((string) $v)), (array) ($raw['leftItems'] ?? []))));
            $right = array_values(array_filter(array_map(fn ($v) => trim($this->forceUtf8((string) $v)), (array) ($raw['rightItems'] ?? []))));
            if (count($left) < 2 || count($right) < 2 || count($left) !== count($right)) {
                $left = ['A', 'B'];
                $right = ['1', '2'];
            }

            $correctMap = (array) ($raw['correctMap'] ?? []);
            $normalizedMap = [];
            foreach ($left as $idx => $label) {
                $mapped = (int) ($correctMap[$idx] ?? $idx);
                if ($mapped < 0 || $mapped >= count($right)) {
                    $mapped = $idx;
                }
                $normalizedMap[(string) $idx] = $mapped;
            }

            return [
                'type' => 'dragdrop',
                'question_text' => $questionText,
                'options' => [
                    'left' => $left,
                    'right' => $right,
                ],
                'correct_answer' => json_encode($normalizedMap, JSON_UNESCAPED_UNICODE),
                'duration_sec' => $durationSec,
                'xp' => $xp,
                'double_xp' => $doubleXp,
            ];
        }

        $options = array_values(array_filter(array_map(fn ($v) => trim($this->forceUtf8((string) $v)), (array) ($raw['options'] ?? []))));
        if (count($options) < 2) {
            $options = ['Secenek 1', 'Secenek 2', 'Secenek 3', 'Secenek 4'];
        }

        $correctIndex = (int) ($raw['correctIndex'] ?? -1);
        if ($correctIndex < 0 || $correctIndex >= count($options)) {
            $correctIndex = 0;
        }
        $correctLetter = chr(65 + $correctIndex);

        return [
            'type' => 'multiple',
            'question_text' => $questionText,
            'options' => $options,
            'correct_answer' => $correctLetter,
            'duration_sec' => $durationSec,
            'xp' => $xp,
            'double_xp' => $doubleXp,
        ];
    }

    /**
     * Kahoot'taki gibi en hizli dogru cevap en yuksek puani alir: sorunun
     * suresinin ne kadari kaldiysa (0 ile 1 arasi bir oran) taban XP'nin
     * %50 ile %100'u arasinda bir carpanla odullendirilir. En son anda dogru
     * cevap verilse bile en az yarim puan garanti edilir, boylece dogru
     * cevap vermek her zaman yanlistan/cevapsizdan daha degerlidir.
     */
    private function calculateSpeedXp(LiveQuizSession $session, LiveQuizQuestion $question, int $answeredAtMs): int
    {
        $baseXp = (int) $question->xp * ($question->double_xp ? 2 : 1);
        $durationMs = max(1000, (int) $question->duration_sec * 1000);
        $endsAtMs = (int) ($session->ends_at_ms ?? 0);
        $startedAtMs = $endsAtMs > 0 ? $endsAtMs - $durationMs : $answeredAtMs;

        $elapsedMs = max(0, $answeredAtMs - $startedAtMs);
        $remainingFraction = max(0.0, min(1.0, 1 - ($elapsedMs / $durationMs)));
        $speedMultiplier = 0.5 + (0.5 * $remainingFraction);

        return max(1, (int) round($baseXp * $speedMultiplier));
    }

    private function evaluateAnswer(Request $request, LiveQuizQuestion $question): array
    {
        if ($question->type === 'dragdrop') {
            $selectedMap = [];
            foreach ((array) $request->input('dragdrop', []) as $leftIndex => $rightIndex) {
                $selectedMap[(string) $leftIndex] = (int) $rightIndex;
            }
            ksort($selectedMap);

            $correctMap = json_decode((string) $question->correct_answer, true) ?: [];
            ksort($correctMap);

            return [
                'selected_answer' => json_encode($selectedMap, JSON_UNESCAPED_UNICODE),
                'is_correct' => $selectedMap === $correctMap,
            ];
        }

        $selected = Str::upper((string) $request->input('answer', ''));
        $correct = Str::upper((string) $question->correct_answer);

        return [
            'selected_answer' => $selected,
            'is_correct' => $selected !== '' && $selected === $correct,
        ];
    }

    private function currentQuestionStats(LiveQuizSession $session): array
    {
        $answers = LiveQuizAnswer::query()
            ->where('live_quiz_session_id', $session->id)
            ->where('question_index', $session->current_index)
            ->get();

        $answered = $answers->count();
        $correct = $answers->where('is_correct', true)->count();

        return [
            'joined' => $session->participants()->count(),
            'answered' => $answered,
            'correct' => $correct,
            'wrong' => max(0, $answered - $correct),
        ];
    }

    private function leaderboardRows(LiveQuizSession $session): array
    {
        $rows = LiveQuizAnswer::query()
            ->selectRaw('student_user_id, COUNT(*) as answered, SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct, SUM(xp_earned) as xp')
            ->where('live_quiz_session_id', $session->id)
            ->groupBy('student_user_id')
            ->orderByDesc('xp')
            ->orderByDesc('correct')
            ->get();

        return $rows->map(function ($r) {
            $student = Student::query()->with('user')->where('user_id', $r->student_user_id)->first();
            return [
                'student_name' => $student?->user?->name ?? ('user_' . $r->student_user_id),
                'answered' => (int) $r->answered,
                'correct' => (int) $r->correct,
                'wrong' => max(0, (int) $r->answered - (int) $r->correct),
                'xp' => (int) $r->xp,
            ];
        })->values()->all();
    }

    private function writeQuizToStudentReports(LiveQuizSession $session): void
    {
        $rows = LiveQuizAnswer::query()
            ->selectRaw('student_user_id, COUNT(*) as answered, SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct, SUM(xp_earned) as xp')
            ->where('live_quiz_session_id', $session->id)
            ->groupBy('student_user_id')
            ->get();

        foreach ($rows as $r) {
            $uid = (int) $r->student_user_id;
            $report = StudentReport::query()->firstOrCreate(['user_id' => $uid], [
                'total_xp' => 0,
                'total_duration_ms' => 0,
                'completion_percent' => 0,
                'meta' => [],
            ]);
            $meta = (array) ($report->meta ?? []);
            $meta['quizSessionsCompleted'] = (int) ($meta['quizSessionsCompleted'] ?? 0) + 1;
            $meta['quizAnswered'] = (int) ($meta['quizAnswered'] ?? 0) + (int) $r->answered;
            $meta['quizCorrect'] = (int) ($meta['quizCorrect'] ?? 0) + (int) $r->correct;
            $meta['quizWrong'] = (int) ($meta['quizWrong'] ?? 0) + max(0, (int) $r->answered - (int) $r->correct);
            $meta['quizTotalXP'] = (int) ($meta['quizTotalXP'] ?? 0) + (int) $r->xp;
            $meta['lastQuizTitle'] = (string) $session->quiz->title;
            $meta['lastQuizAt'] = now()->toIso8601String();

            $report->meta = $meta;
            $report->save();
        }
    }

    private function forceUtf8(string $text): string
    {
        return (string) Utf8Text::normalize($text);
    }

    private function syncSessionByTimer(LiveQuizSession $session): LiveQuizSession
    {
        $session->loadMissing('quiz.questions');
        if ($session->status !== 'live') {
            return $session;
        }

        $questions = $session->quiz?->questions?->sortBy('sort_order')->values() ?? collect();
        if ($questions->isEmpty()) {
            $session->update([
                'status' => 'finished',
                'is_locked' => true,
                'finished_at_ms' => $this->nowMs(),
            ]);
            return $session->fresh(['quiz.questions']);
        }

        $nowMs = $this->nowMs();
        $currentIndex = (int) $session->current_index;
        $endsAtMs = (int) ($session->ends_at_ms ?? 0);
        if ($endsAtMs <= 0) {
            $duration = max(5, (int) ($questions[$currentIndex]->duration_sec ?? 30));
            $session->update(['ends_at_ms' => $nowMs + ($duration * 1000)]);
            return $session->fresh(['quiz.questions']);
        }

        while ($session->status === 'live' && $nowMs >= $endsAtMs) {
            $nextIndex = $currentIndex + 1;
            if ($nextIndex >= $questions->count()) {
                $session->update([
                    'status' => 'finished',
                    'is_locked' => true,
                    'finished_at_ms' => $nowMs,
                ]);
                $this->writeQuizToStudentReports($session->fresh('quiz'));
                break;
            }

            $duration = max(5, (int) ($questions[$nextIndex]->duration_sec ?? 30));
            $currentIndex = $nextIndex;
            $endsAtMs = $endsAtMs + ($duration * 1000);

            $session->update([
                'current_index' => $currentIndex,
                'is_locked' => false,
                'ends_at_ms' => $endsAtMs,
            ]);
        }

        return $session->fresh(['quiz.questions']);
    }

    private function nowMs(): int
    {
        return (int) floor(microtime(true) * 1000);
    }

    private function studentCanJoinSession(LiveQuizSession $session, int $studentUserId): bool
    {
        $quiz = $session->quiz()->first();
        if (!$quiz) {
            return false;
        }
        $student = Student::query()->where('user_id', $studentUserId)->first();
        if (!$student) {
            return false;
        }
        if (empty($quiz->school_class_id)) {
            return true;
        }

        return (int) $student->school_class_id === (int) $quiz->school_class_id;
    }

    private function studentRankInSession(LiveQuizSession $session, int $studentUserId): array
    {
        $rows = LiveQuizAnswer::query()
            ->selectRaw('student_user_id, SUM(xp_earned) as xp, SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct')
            ->where('live_quiz_session_id', $session->id)
            ->groupBy('student_user_id')
            ->orderByDesc('xp')
            ->orderByDesc('correct')
            ->get()
            ->values();

        $rank = 0;
        foreach ($rows as $i => $row) {
            if ((int) $row->student_user_id === $studentUserId) {
                $rank = $i + 1;
                break;
            }
        }

        return [
            'rank' => $rank,
            'total' => $rows->count(),
        ];
    }

    private function resolveCorrectAnswerText(LiveQuizQuestion $question): string
    {
        if ($question->type === 'truefalse') {
            return Str::upper((string) $question->correct_answer) === 'A' ? 'Dogru' : 'Yanlis';
        }

        if ($question->type === 'dragdrop') {
            return 'Surukle birak eslesmesi';
        }

        $options = is_array($question->options) ? array_values($question->options) : [];
        $letter = Str::upper((string) $question->correct_answer);
        $index = ord($letter) - 65;
        if ($index >= 0 && $index < count($options)) {
            return (string) $options[$index];
        }

        return $letter;
    }

    private function resolveSelectedAnswerText(LiveQuizQuestion $question, string $selectedAnswer): string
    {
        if ($question->type === 'truefalse') {
            return Str::upper($selectedAnswer) === 'A' ? 'Dogru' : 'Yanlis';
        }

        if ($question->type === 'dragdrop') {
            return 'Surukle birak cevabi';
        }

        $options = is_array($question->options) ? array_values($question->options) : [];
        $letter = Str::upper($selectedAnswer);
        $index = ord($letter) - 65;
        if ($index >= 0 && $index < count($options)) {
            return (string) $options[$index];
        }

        return $letter;
    }
}
