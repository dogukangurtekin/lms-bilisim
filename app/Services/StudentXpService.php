<?php

namespace App\Services;

use App\Models\CompetitionParticipant;
use App\Models\ContentProgress;
use App\Models\Grade;
use App\Models\LiveQuizAnswer;
use App\Models\RaceResult;
use App\Models\Student;
use App\Models\StudentReport;
use App\Models\UserProfile;
use App\Models\UserXpLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentXpService
{
    public function earned(Student $student): int
    {
        return $this->earnedFor(collect([$student]))[$student->id] ?? 0;
    }

    /**
     * Kazanılan XP'yi kalıcı sayaçlara (user_profiles.xp, student_reports.total_xp)
     * yazar. earnedFor() sayaçlar ile kayıt toplamının en büyüğünü aldığından,
     * kayıt tablolarına (ContentProgress, Grade, UserXpLog) yazılan her XP buradan
     * da geçmeli; yoksa sayaç kayıt toplamını aşınca yeni XP hesaba yansımaz.
     */
    public function award(?int $userId, int $delta, bool $includeReport = true): void
    {
        if (! $userId || $delta <= 0) {
            return;
        }

        DB::transaction(function () use ($userId, $delta, $includeReport): void {
            UserProfile::query()->lockForUpdate()->firstOrCreate(
                ['user_id' => $userId],
                ['role' => 'student', 'xp' => 0]
            )->increment('xp', $delta);

            if ($includeReport) {
                StudentReport::query()->lockForUpdate()->firstOrCreate(
                    ['user_id' => $userId],
                    ['total_xp' => 0, 'total_duration_ms' => 0, 'completion_percent' => 0, 'meta' => []]
                )->increment('total_xp', $delta);
            }
        });
    }

    public function available(Student $student): int
    {
        return max(0, $this->earned($student) - (int) ($student->avatar_xp_spent ?? 0));
    }

    /**
     * @param  Collection<int, Student>  $students
     * @return array<int, int>
     */
    public function earnedFor(Collection $students): array
    {
        if ($students->isEmpty()) {
            return [];
        }

        $studentIds = $students->pluck('id')->filter()->values();
        $userIds = $students->pluck('user_id')->filter()->values();

        $gradeXp = Grade::query()
            ->selectRaw('student_id, ROUND(SUM(score)) as xp')
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id')
            ->pluck('xp', 'student_id');
        $contentXp = ContentProgress::query()
            ->selectRaw('user_id, SUM(xp_awarded) as xp')
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->pluck('xp', 'user_id');
        $quizXp = LiveQuizAnswer::query()
            ->selectRaw('student_user_id as user_id, SUM(xp_earned) as xp')
            ->whereIn('student_user_id', $userIds)
            ->groupBy('student_user_id')
            ->pluck('xp', 'user_id');
        $competitionXp = CompetitionParticipant::query()
            ->selectRaw('student_user_id as user_id, SUM(xp_earned) as xp')
            ->whereIn('student_user_id', $userIds)
            ->groupBy('student_user_id')
            ->pluck('xp', 'user_id');
        $keyboardRaceXp = RaceResult::query()
            ->selectRaw('user_id, SUM(xp_earned) as xp')
            ->whereNotNull('user_id')
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->pluck('xp', 'user_id');
        $codingXp = UserXpLog::query()
            ->selectRaw('user_id, SUM(xp_delta) as xp')
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->pluck('xp', 'user_id');
        $profileXp = UserProfile::query()
            ->whereIn('user_id', $userIds)
            ->pluck('xp', 'user_id');
        $reportXp = StudentReport::query()
            ->whereIn('user_id', $userIds)
            ->pluck('total_xp', 'user_id');

        return $students->mapWithKeys(function (Student $student) use ($gradeXp, $contentXp, $quizXp, $competitionXp, $keyboardRaceXp, $codingXp, $profileXp, $reportXp) {
            $computed =
                (int) ($gradeXp[$student->id] ?? 0)
                + (int) ($contentXp[$student->user_id] ?? 0)
                + (int) ($quizXp[$student->user_id] ?? 0)
                + (int) ($competitionXp[$student->user_id] ?? 0)
                + (int) ($keyboardRaceXp[$student->user_id] ?? 0)
                + (int) ($codingXp[$student->user_id] ?? 0);

            return [$student->id => max(
                0,
                $computed,
                (int) ($profileXp[$student->user_id] ?? 0),
                (int) ($reportXp[$student->user_id] ?? 0),
            )];
        })->all();
    }

    /**
     * @param  Collection<int, Student>  $students
     * @return array<int, int>
     */
    public function availableFor(Collection $students): array
    {
        $earned = $this->earnedFor($students);

        return $students->mapWithKeys(fn (Student $student) => [
            $student->id => max(0, ($earned[$student->id] ?? 0) - (int) ($student->avatar_xp_spent ?? 0)),
        ])->all();
    }
}
