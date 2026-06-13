<?php

namespace Tests\Feature;

use App\Models\ActivityQuestion;
use App\Models\CodingActivity;
use App\Models\QuestionOption;
use App\Models\User;
use App\Services\CodingActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodingActivityFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_task_awards_xp_once_when_all_answers_are_correct(): void
    {
        $user = User::factory()->create();

        $activity = CodingActivity::create([
            'type' => 'daily_task',
            'title' => 'Test Günlük',
            'base_xp' => 20,
            'is_active' => true,
            'is_random_pool' => true,
            'lesson_pages' => ['Kısa konu anlatımı'],
        ]);

        $q1 = ActivityQuestion::create([
            'coding_activity_id' => $activity->id,
            'question_type' => 'single_choice',
            'prompt' => '2 + 2 kaç eder?',
            'points' => 10,
            'order_no' => 1,
            'answer_key' => ['answer' => 'A'],
        ]);

        QuestionOption::create(['activity_question_id' => $q1->id, 'option_key' => 'A', 'label' => '4', 'is_correct' => true, 'order_no' => 1]);
        QuestionOption::create(['activity_question_id' => $q1->id, 'option_key' => 'B', 'label' => '5', 'is_correct' => false, 'order_no' => 2]);

        $q2 = ActivityQuestion::create([
            'coding_activity_id' => $activity->id,
            'question_type' => 'short_text',
            'prompt' => 'Merhaba yazın',
            'points' => 10,
            'order_no' => 2,
            'answer_key' => ['answer' => 'merhaba'],
        ]);

        $service = app(CodingActivityService::class);
        $activity->load('questions.options');

        $firstAttempt = $service->submitAttempt($activity, $user->id, [
            $q1->id => 'A',
            $q2->id => 'merhaba',
        ]);

        $this->assertTrue((bool) $firstAttempt->all_correct);
        $this->assertSame(24, (int) $firstAttempt->awarded_xp);
        $this->assertSame('Günlük egzersizi yaptınız. Bir sonraki egzersiz için yarını bekleyin.', (string) $firstAttempt->feedback_message);

        $secondAttempt = $service->submitAttempt($activity, $user->id, [
            $q1->id => 'A',
            $q2->id => 'merhaba',
        ]);

        $this->assertFalse((bool) $secondAttempt->awarded_xp);
        $this->assertSame('Günlük egzersizi yaptınız. Bir sonraki egzersiz için yarını bekleyin.', (string) $secondAttempt->feedback_message);
    }

    public function test_daily_task_wrong_answer_does_not_award_xp_and_returns_guidance(): void
    {
        $user = User::factory()->create();

        $activity = CodingActivity::create([
            'type' => 'daily_task',
            'title' => 'Test Günlük 2',
            'base_xp' => 20,
            'is_active' => true,
            'is_random_pool' => true,
            'lesson_pages' => ['Kısa konu anlatımı'],
        ]);

        $q1 = ActivityQuestion::create([
            'coding_activity_id' => $activity->id,
            'question_type' => 'single_choice',
            'prompt' => '2 + 2 kaç eder?',
            'points' => 10,
            'order_no' => 1,
            'answer_key' => ['answer' => 'A'],
        ]);

        QuestionOption::create(['activity_question_id' => $q1->id, 'option_key' => 'A', 'label' => '4', 'is_correct' => true, 'order_no' => 1]);
        QuestionOption::create(['activity_question_id' => $q1->id, 'option_key' => 'B', 'label' => '5', 'is_correct' => false, 'order_no' => 2]);

        $service = app(CodingActivityService::class);
        $activity->load('questions.options');

        $attempt = $service->submitAttempt($activity, $user->id, [
            $q1->id => 'B',
        ]);

        $this->assertFalse((bool) $attempt->all_correct);
        $this->assertSame(0, (int) $attempt->awarded_xp);
        $this->assertSame('Sorulara odaklanın, tekrar yapmanızda fayda var.', (string) $attempt->feedback_message);
    }
}
