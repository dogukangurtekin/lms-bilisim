<?php

namespace Tests\Feature;

use App\Models\LiveQuiz;
use App\Models\LiveQuizParticipant;
use App\Models\LiveQuizQuestion;
use App\Models\LiveQuizSession;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTimeBarTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_screen_shows_the_countdown_bar_with_the_phase_duration(): void
    {
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $teacher = User::query()->create(['role_id' => $teacherRole->id, 'name' => 'Öğretmen', 'email' => 'tb-t@test.local', 'password' => 'secret123', 'is_active' => true]);
        $student = User::query()->create(['role_id' => $studentRole->id, 'name' => 'Öğrenci', 'email' => 'tb-s@test.local', 'password' => 'secret123', 'is_active' => true]);
        $quiz = LiveQuiz::query()->create(['teacher_user_id' => $teacher->id, 'title' => 'Süre Quiz', 'join_mode' => 'code', 'status' => 'active']);
        LiveQuizQuestion::query()->create(['live_quiz_id' => $quiz->id, 'sort_order' => 0, 'type' => 'multiple', 'question_text' => 'Soru', 'options' => ['A', 'B'], 'correct_answer' => 'A', 'duration_sec' => 25, 'xp' => 10, 'double_xp' => false]);
        $session = LiveQuizSession::query()->create([
            'live_quiz_id' => $quiz->id, 'teacher_user_id' => $teacher->id, 'join_code' => 'TIME01', 'status' => 'live', 'phase' => 'question',
            'current_index' => 0, 'is_locked' => false, 'started_at_ms' => 1, 'ends_at_ms' => (int) floor(microtime(true) * 1000) + 25000,
        ]);
        LiveQuizParticipant::query()->create(['live_quiz_session_id' => $session->id, 'student_user_id' => $student->id, 'joined_at_ms' => 1]);

        $this->actingAs($student)->get(route('student.live-quiz.play', $session))
            ->assertOk()->assertSee('id="lqTimeBar"', false)->assertSee('data-total-ms="25000"', false)->assertSee('Kalan süre')->assertSee('id="lqTimeFill"', false);

        // sonuç aşamasında da (5 sn) çubuk görünür
        $session->update(['phase' => 'results', 'is_locked' => true, 'ends_at_ms' => (int) floor(microtime(true) * 1000) + 5000]);
        $this->actingAs($student)->get(route('student.live-quiz.play', $session))
            ->assertOk()->assertSee('data-total-ms="5000"', false)->assertSee('Sıradaki soruya');
    }
}
