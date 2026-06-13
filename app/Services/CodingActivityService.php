<?php

namespace App\Services;

use App\Jobs\UpdateActivityLeaderboard;
use App\Models\ActivityAttempt;
use App\Models\ActivityQuestion;
use App\Models\CodingActivity;
use App\Models\DailyActivityAssignment;
use App\Models\StudentReport;
use App\Models\UserStreak;
use App\Models\UserXpLog;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CodingActivityService
{
    public function resolveTodayActivityForStudent(): ?CodingActivity
    {
        $today = Carbon::today('Europe/Istanbul')->toDateString();

        $assigned = DailyActivityAssignment::with('activity.questions.options')
            ->whereDate('assignment_date', $today)
            ->where('target_role', 'student')
            ->first();

        if ($assigned?->activity?->is_active) {
            return $assigned->activity;
        }

        return CodingActivity::with('questions.options')
            ->where('is_active', true)
            ->where('is_random_pool', true)
            ->whereHas('questions')
            ->inRandomOrder()
            ->first();
    }

    public function submitAttempt(CodingActivity $activity, int $userId, array $answers, int $durationSeconds = 0): ActivityAttempt
    {
        return DB::transaction(function () use ($activity, $userId, $answers, $durationSeconds): ActivityAttempt {
            $today = Carbon::today('Europe/Istanbul')->toDateString();
            $alreadyCompletedToday = $activity->type === 'daily_task'
                && UserXpLog::query()
                    ->where('user_id', $userId)
                    ->where('coding_activity_id', $activity->id)
                    ->whereDate('awarded_on', $today)
                    ->exists();

            $attempt = ActivityAttempt::create([
                'coding_activity_id' => $activity->id,
                'user_id' => $userId,
                'status' => 'submitted',
                'started_at' => now(),
                'submitted_at' => now(),
                'duration_seconds' => max(0, $durationSeconds),
            ]);

            $score = 0;
            $correct = 0;
            $wrong = 0;
            $maxScore = 0;
            $wrongDetails = [];
            foreach ($activity->questions as $question) {
                $given = $answers[$question->id] ?? null;
                $result = $this->evaluateQuestion($question, $given);
                $points = $result['awarded_points'];
                $isCorrect = $result['is_correct'];
                $score += $points;
                $maxScore += (float) $question->points;
                $correct += $isCorrect ? 1 : 0;
                $wrong += $isCorrect ? 0 : 1;
                if (! $isCorrect) {
                    $wrongDetails[] = $this->buildWrongDetail($question, $given);
                }
                $attempt->answers()->create([
                    'activity_question_id' => $question->id,
                    'answer_payload' => ['value' => $given],
                    'awarded_points' => $points,
                ]);
            }

            $attempt->update(['score' => max(0, (int) round($score)), 'status' => 'finished']);

            $isFullyCorrect = $activity->questions->isNotEmpty() && $correct === $activity->questions->count() && $wrong === 0;
            $awardedXp = 0;
            $feedback = 'Sorulara odaklanın, tekrar yapmanızda fayda var.';
            if ($alreadyCompletedToday) {
                $feedback = 'Günlük egzersizi yaptınız. Bir sonraki egzersiz için yarını bekleyin.';
            } elseif ($isFullyCorrect) {
                $awardedXp = $this->awardXpAndStreak($activity, $userId, $attempt->score);
                $this->writeStudentReport($userId, $activity, $attempt->score, $awardedXp, $correct, $wrong, $durationSeconds);
                $feedback = 'Günlük egzersizi yaptınız. Bir sonraki egzersiz için yarını bekleyin.';
            } else {
                $this->writeStudentReport($userId, $activity, $attempt->score, 0, $correct, $wrong, $durationSeconds);
            }

            if ($isFullyCorrect && ! $alreadyCompletedToday) {
                UpdateActivityLeaderboard::dispatch($attempt->id);
            }

            $attempt->refresh();
            $attempt->setAttribute('awarded_xp', $awardedXp);
            $attempt->setAttribute('all_correct', $isFullyCorrect);
            $attempt->setAttribute('already_completed_today', $alreadyCompletedToday);
            $attempt->setAttribute('feedback_message', $feedback);
            $attempt->setAttribute('max_score', (int) round($maxScore));
            $attempt->setAttribute('wrong_details', array_values(array_filter($wrongDetails)));
            return $attempt;
        });
    }

    private function evaluateQuestion(ActivityQuestion $question, mixed $given): array
    {
        $max = (float) $question->points;
        $key = (array) ($question->answer_key ?? []);

        if ($question->question_type === 'multi_choice') {
            $correct = collect(Arr::wrap($key['correct'] ?? []))
                ->map(fn ($v) => strtoupper(trim((string) $v)))
                ->filter(fn ($v) => $v !== '')
                ->unique()
                ->values();
            $picked = collect(Arr::wrap($given))
                ->map(fn ($v) => strtoupper(trim((string) $v)))
                ->filter(fn ($v) => $v !== '')
                ->unique()
                ->values();
            if ($correct->isEmpty()) {
                return ['awarded_points' => 0, 'is_correct' => false];
            }

            $hit = $picked->intersect($correct)->count();
            $wrong = $picked->diff($correct)->count();
            $awarded = max(0, $max * (($hit / $correct->count()) - ($wrong * 0.25)));
            $isCorrect = $picked->sort()->values()->all() === $correct->sort()->values()->all();
            return ['awarded_points' => $awarded, 'is_correct' => $isCorrect];
        }

        if ($question->question_type === 'single_choice') {
            $givenKey = strtoupper(trim((string) $given));
            $storedCorrectKeys = collect(Arr::wrap($key['correct'] ?? []))
                ->map(fn ($v) => strtoupper(trim((string) $v)))
                ->filter(fn ($v) => $v !== '')
                ->values();

            if ($storedCorrectKeys->isNotEmpty()) {
                return [
                    'awarded_points' => $storedCorrectKeys->contains($givenKey) ? $max : 0,
                    'is_correct' => $storedCorrectKeys->contains($givenKey),
                ];
            }

            $correctLabels = collect($question->options)
                ->filter(fn ($opt) => (bool) ($opt->is_correct ?? false))
                ->pluck('label')
                ->map(fn ($v) => $this->normalizeAnswerText((string) $v, $question->question_type))
                ->filter(fn ($v) => $v !== '')
                ->values();

            $givenLabel = $this->normalizeAnswerText((string) $given, $question->question_type);
            if ($correctLabels->isNotEmpty()) {
                $isCorrect = $correctLabels->contains($givenLabel);
                return ['awarded_points' => $isCorrect ? $max : 0, 'is_correct' => $isCorrect];
            }
        }

        $normalized = $this->normalizeAnswerText((string) $given, $question->question_type);
        $answer = $this->normalizeAnswerText((string) ($key['answer'] ?? ''), $question->question_type);
        $isCorrect = $normalized !== '' && $normalized === $answer;
        return ['awarded_points' => $isCorrect ? $max : 0, 'is_correct' => $isCorrect];
    }

    private function normalizeAnswerText(string $value, string $questionType): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = mb_strtolower($value, 'UTF-8');
        $value = str_replace(
            ['ç', 'ğı', 'ğ', 'ı', 'iÌ‡', 'ö', 'ş', 'ü'],
            ['c', 'gi', 'g', 'i', 'i', 'o', 's', 'u'],
            $value
        );
        $value = preg_replace('/[^\p{L}\p{N}]+/u', '', $value) ?? '';

        if ($questionType === 'code_output') {
            $value = preg_replace('/\s+/', '', $value) ?? $value;
        }

        return $value;
    }

    private function buildWrongDetail(ActivityQuestion $question, mixed $given): array
    {
        $expected = '';
        $givenText = is_array($given) ? implode(', ', array_map(fn ($v) => (string) $v, $given)) : (string) $given;

        if ($question->question_type === 'single_choice') {
            $correctOptions = $question->options->filter(fn ($opt) => (bool) ($opt->is_correct ?? false))->values();
            $expected = $correctOptions->first()?->option_key ?: $correctOptions->first()?->label ?: '';
            $givenKey = strtoupper(trim((string) $given));
            if ($givenKey !== '') {
                $givenText = $givenKey;
            }
        } elseif ($question->question_type === 'multi_choice') {
            $expected = $question->options
                ->filter(fn ($opt) => (bool) ($opt->is_correct ?? false))
                ->pluck('option_key')
                ->implode(', ');
        } else {
            $expected = (string) data_get($question->answer_key, 'answer', '');
        }

        return [
            'question' => (string) $question->prompt,
            'type' => (string) $question->question_type,
            'expected' => $expected !== '' ? $expected : '-',
            'given' => trim($givenText) !== '' ? $givenText : '-',
        ];
    }

    private function writeStudentReport(int $userId, CodingActivity $activity, int $score, int $xp, int $correct, int $wrong, int $durationSeconds): void
    {
        $report = StudentReport::query()->firstOrCreate(['user_id' => $userId], [
            'total_xp' => 0,
            'total_duration_ms' => 0,
            'completion_percent' => 0,
            'meta' => [],
        ]);

        $meta = (array) ($report->meta ?? []);
        $logs = (array) ($meta['dailyCodingLogs'] ?? []);
        $logs[] = [
            'date' => now('Europe/Istanbul')->toDateString(),
            'activity_id' => $activity->id,
            'title' => $activity->title,
            'type' => $activity->type,
            'score' => $score,
            'xp' => $xp,
            'correct' => $correct,
            'wrong' => $wrong,
            'duration_seconds' => $durationSeconds,
        ];
        $meta['dailyCodingLogs'] = array_slice($logs, -90);

        $report->total_xp = (int) $report->total_xp + $xp;
        $report->total_duration_ms = (int) $report->total_duration_ms + ($durationSeconds * 1000);
        $report->meta = $meta;
        $report->save();
    }

    private function awardXpAndStreak(CodingActivity $activity, int $userId, int $score): int
    {
        $today = Carbon::today('Europe/Istanbul')->toDateString();
        $alreadyAwarded = UserXpLog::query()->where('user_id', $userId)->where('coding_activity_id', $activity->id)->whereDate('awarded_on', $today)->exists();
        if ($activity->type === 'daily_task' && $alreadyAwarded) return 0;

        $xp = (int) max(0, round($activity->base_xp + ($score * 0.2)));
        UserXpLog::create(['user_id' => $userId, 'coding_activity_id' => $activity->id, 'xp_delta' => $xp, 'reason' => 'coding_activity_complete', 'awarded_on' => $today]);
        $this->updateStreak($userId);
        return $xp;
    }

    private function updateStreak(int $userId): void
    {
        $today = Carbon::today('Europe/Istanbul');
        $streak = UserStreak::firstOrCreate(['user_id' => $userId]);
        $last = $streak->last_activity_date ? Carbon::parse($streak->last_activity_date) : null;
        if ($last && $last->isSameDay($today)) return;
        $streak->current_streak = ($last && $last->copy()->addDay()->isSameDay($today)) ? ($streak->current_streak + 1) : 1;
        $streak->best_streak = max($streak->best_streak, $streak->current_streak);
        $streak->last_activity_date = $today->toDateString();
        $streak->save();
    }
}
