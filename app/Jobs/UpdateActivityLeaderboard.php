<?php

namespace App\Jobs;

use App\Models\ActivityAttempt;
use App\Models\Leaderboard;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateActivityLeaderboard implements ShouldQueue
{
    use Queueable;
    public function __construct(public int $attemptId) {}
    public function handle(): void
    {
        $attempt = ActivityAttempt::find($this->attemptId);
        if (! $attempt) return;

        Leaderboard::updateOrCreate(
            ['coding_activity_id' => $attempt->coding_activity_id, 'user_id' => $attempt->user_id],
            ['score' => $attempt->score, 'duration_seconds' => $attempt->duration_seconds]
        );
    }
}
