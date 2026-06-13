<?php

namespace Tests\Unit;

use App\Models\ActivityQuestion;
use App\Models\CodingActivity;
use App\Models\Role;
use App\Models\User;
use App\Services\CodingActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodingActivityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_attempt_creates_score_and_xp(): void
    {
        $role = Role::create(['slug' => 'student', 'name' => 'Student']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $activity = CodingActivity::create(['type' => 'quiz', 'title' => 'Quiz', 'base_xp' => 10, 'is_active' => true]);
        $q = ActivityQuestion::create(['coding_activity_id' => $activity->id, 'question_type' => 'short_text', 'prompt' => 'x?', 'answer_key' => ['answer' => 'a'], 'points' => 10, 'order_no' => 1]);

        $attempt = app(CodingActivityService::class)->submitAttempt($activity, $user->id, [$q->id => 'a'], 5);

        $this->assertEquals(10, $attempt->score);
        $this->assertDatabaseHas('user_xp_logs', ['user_id' => $user->id, 'coding_activity_id' => $activity->id]);
    }
}
