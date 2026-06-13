<?php
namespace App\Events;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class LiveQuizScoreUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public int $roomId, public array $leaderboard) {}
    public function broadcastOn(): array { return [new PrivateChannel('live-quiz-room.'.$this->roomId)]; }
    public function broadcastAs(): string { return 'live.quiz.score.updated'; }
}
