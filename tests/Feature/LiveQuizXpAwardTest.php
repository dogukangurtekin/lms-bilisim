<?php

namespace Tests\Feature;

use App\Models\LiveQuiz;
use App\Models\LiveQuizAnswer;
use App\Models\LiveQuizParticipant;
use App\Models\LiveQuizQuestion;
use App\Models\LiveQuizSession;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveQuizXpAwardTest extends TestCase
{
    use RefreshDatabase;

    public function test_correct_quiz_answer_is_persisted_to_student_profile_once(): void
    {
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $teacher = $this->user($teacherRole->id, 'Öğretmen', 'quiz-xp-teacher@test.local');
        $student = $this->user($studentRole->id, 'Öğrenci', 'quiz-xp-student@test.local');

        $quiz = LiveQuiz::query()->create([
            'teacher_user_id' => $teacher->id,
            'title' => 'XP Quiz',
            'join_mode' => 'code',
            'status' => 'active',
        ]);
        LiveQuizQuestion::query()->create([
            'live_quiz_id' => $quiz->id,
            'sort_order' => 0,
            'type' => 'multiple',
            'question_text' => 'Doğru seçenek hangisi?',
            'options' => ['A seçeneği', 'B seçeneği'],
            'correct_answer' => 'A',
            'duration_sec' => 30,
            'xp' => 20,
            'double_xp' => false,
        ]);
        $session = LiveQuizSession::query()->create([
            'live_quiz_id' => $quiz->id,
            'teacher_user_id' => $teacher->id,
            'join_code' => 'QZXP01',
            'status' => 'live',
            'current_index' => 0,
            'is_locked' => false,
            'started_at_ms' => $this->nowMs(),
            'ends_at_ms' => $this->nowMs() + 30000,
        ]);
        LiveQuizParticipant::query()->create([
            'live_quiz_session_id' => $session->id,
            'student_user_id' => $student->id,
            'joined_at_ms' => $this->nowMs(),
        ]);

        UserProfile::query()->where('user_id', $student->id)->delete();

        $this->actingAs($student)
            ->post(route('student.live-quiz.answer', $session), [
                'question_index' => 0,
                'answer' => 'A',
            ])
            ->assertRedirect();

        $answer = LiveQuizAnswer::query()->firstOrFail();
        $this->assertTrue($answer->is_correct);
        $this->assertGreaterThan(0, $answer->xp_earned);
        $this->assertSame(
            (int) $answer->xp_earned,
            (int) UserProfile::query()->findOrFail($student->id)->xp
        );

        $this->actingAs($student)
            ->post(route('student.live-quiz.answer', $session), [
                'question_index' => 0,
                'answer' => 'A',
            ])
            ->assertRedirect();

        $this->assertSame(
            (int) $answer->xp_earned,
            (int) UserProfile::query()->findOrFail($student->id)->xp
        );
    }

    private function user(int $roleId, string $name, string $email): User
    {
        return User::query()->create([
            'role_id' => $roleId,
            'name' => $name,
            'email' => $email,
            'password' => 'secret123',
            'is_active' => true,
        ]);
    }

    private function nowMs(): int
    {
        return (int) floor(microtime(true) * 1000);
    }
}
