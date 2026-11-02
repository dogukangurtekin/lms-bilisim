<?php

namespace Tests\Feature;

use App\Models\LiveQuiz;
use App\Models\LiveQuizQuestion;
use App\Models\LiveQuizSession;
use App\Models\Role;
use App\Models\User;
use App\Support\QuizSoundSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizSoundTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        @unlink(storage_path('app/quiz-sound.json'));
        parent::tearDown();
    }

    public function test_only_admin_can_change_sound_settings_and_screens_get_the_config(): void
    {
        @unlink(storage_path('app/quiz-sound.json'));
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $admin = User::query()->create(['role_id' => $adminRole->id, 'name' => 'Yönetici', 'email' => 'snd-a@test.local', 'password' => 'secret123', 'is_active' => true]);
        $teacher = User::query()->create(['role_id' => $teacherRole->id, 'name' => 'Öğretmen', 'email' => 'snd-t@test.local', 'password' => 'secret123', 'is_active' => true]);

        $this->assertSame(['enabled' => true, 'music' => true, 'effects' => true, 'volume' => 70], QuizSoundSetting::all());

        $this->actingAs($admin)->get(route('live-quiz.index'))->assertOk()->assertSee('Ses ve Müzik');
        $this->actingAs($teacher)->get(route('live-quiz.index'))->assertOk()->assertDontSee('Ses ve Müzik');

        $this->actingAs($teacher)->post(route('live-quiz.sound.update'), ['enabled' => 1])->assertForbidden();

        $this->actingAs($admin)->post(route('live-quiz.sound.update'), ['enabled' => 1, 'effects' => 1, 'volume' => 40])
            ->assertRedirect(route('live-quiz.index'));
        $this->assertSame(['enabled' => true, 'music' => false, 'effects' => true, 'volume' => 40], QuizSoundSetting::all());

        $quiz = LiveQuiz::query()->create(['teacher_user_id' => $teacher->id, 'title' => 'Ses Quiz', 'join_mode' => 'code', 'status' => 'active']);
        LiveQuizQuestion::query()->create(['live_quiz_id' => $quiz->id, 'sort_order' => 0, 'type' => 'multiple', 'question_text' => 'Soru', 'options' => ['A', 'B'], 'correct_answer' => 'A', 'duration_sec' => 20, 'xp' => 10, 'double_xp' => false]);
        $session = LiveQuizSession::query()->create(['live_quiz_id' => $quiz->id, 'teacher_user_id' => $teacher->id, 'join_code' => 'SND001', 'status' => 'lobby', 'current_index' => 0, 'is_locked' => false]);

        $this->actingAs($teacher)->get(route('live-quiz.session.show', $session))
            ->assertOk()->assertSee('js/quiz-sound.js', false)->assertSee('"volume":40', false);
    }
}
