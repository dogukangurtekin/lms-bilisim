<?php

namespace Tests\Feature;

use App\Models\LiveQuiz;
use App\Models\LiveQuizQuestion;
use App\Models\LiveQuizSession;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveQuizCompactStartFormTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private LiveQuiz $quiz;

    protected function setUp(): void
    {
        parent::setUp();
        $role = Role::query()->firstOrCreate(['slug' => 'teacher'], ['name' => 'Teacher']);
        $this->teacher = User::query()->create(['role_id' => $role->id, 'name' => 'Hoca', 'email' => 'h@t.l', 'password' => 'secret123', 'is_active' => true]);
        $this->quiz = LiveQuiz::query()->create(['teacher_user_id' => $this->teacher->id, 'title' => 'Kompakt Quiz', 'join_mode' => 'code', 'status' => 'active']);
        for ($i = 0; $i < 5; $i++) {
            LiveQuizQuestion::query()->create(['live_quiz_id' => $this->quiz->id, 'sort_order' => $i, 'type' => 'multiple', 'question_text' => 'S' . $i, 'options' => ['A', 'B'], 'correct_answer' => 'A', 'duration_sec' => 20, 'xp' => 10, 'double_xp' => false]);
        }
    }

    public function test_list_row_has_one_compact_between_select_and_a_single_row_of_actions(): void
    {
        $html = $this->actingAs($this->teacher)->get(route('live-quiz.index'))->assertOk()->getContent();
        $this->assertStringContainsString('name="between"', $html);
        $this->assertStringContainsString('quiz-actions-row', $html);
        $this->assertStringContainsString('⏱ 5 sn bekle', $html);
        $this->assertStringContainsString('🎲 Rastgele oyun', $html);
        $this->assertStringContainsString('compute-it-runner', $html);
        // eski, yer kaplayan iki ayrı seçim kutusu artık yok
        $this->assertStringNotContainsString('name="between_mode"', $html);
        $this->assertStringNotContainsString('name="between_game"', $html);
    }

    public function test_single_select_values_map_to_wait_random_and_a_fixed_game(): void
    {
        $this->actingAs($this->teacher)->post(route('live-quiz.start', $this->quiz), ['between' => 'wait'])->assertRedirect();
        $s = LiveQuizSession::query()->latest('id')->firstOrFail();
        $this->assertSame('wait', $s->between_mode);
        $this->assertNull($s->between_game);

        $this->actingAs($this->teacher)->post(route('live-quiz.start', $this->quiz), ['between' => 'random'])->assertRedirect();
        $s = LiveQuizSession::query()->latest('id')->firstOrFail();
        $this->assertSame('game', $s->between_mode);
        $this->assertNull($s->between_game);

        $this->actingAs($this->teacher)->post(route('live-quiz.start', $this->quiz), ['between' => 'lightbot-runner'])->assertRedirect();
        $s = LiveQuizSession::query()->latest('id')->firstOrFail();
        $this->assertSame('game', $s->between_mode);
        $this->assertSame('lightbot-runner', $s->between_game);

        $before = LiveQuizSession::query()->count();
        $this->actingAs($this->teacher)->post(route('live-quiz.start', $this->quiz), ['between' => 'excel-runner'])->assertSessionHas('err');
        $this->assertSame($before, LiveQuizSession::query()->count());
    }
}
