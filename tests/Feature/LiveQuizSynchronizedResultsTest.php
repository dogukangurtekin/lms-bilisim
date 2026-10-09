<?php

namespace Tests\Feature;

use App\Models\LiveQuiz;
use App\Models\LiveQuizAnswer;
use App\Models\LiveQuizParticipant;
use App\Models\LiveQuizQuestion;
use App\Models\LiveQuizSession;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveQuizSynchronizedResultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_next_closes_answers_then_everyone_advances_after_five_second_results(): void
    {
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $teacher = $this->user($teacherRole->id, 'Öğretmen', 'teacher-sync@test.local');
        $fastStudent = $this->user($studentRole->id, 'Ayşe Yılmaz', 'ayse-sync@test.local');
        $silentStudent = $this->user($studentRole->id, 'Mehmet Kaya', 'mehmet-sync@test.local');

        $quiz = LiveQuiz::query()->create([
            'teacher_user_id' => $teacher->id,
            'title' => 'Senkron Quiz',
            'join_mode' => 'code',
            'status' => 'active',
        ]);

        foreach (['Birinci soru', 'İkinci soru'] as $index => $questionText) {
            LiveQuizQuestion::query()->create([
                'live_quiz_id' => $quiz->id,
                'sort_order' => $index,
                'type' => 'multiple',
                'question_text' => $questionText,
                'options' => ['Doğru', 'Yanlış'],
                'correct_answer' => 'A',
                'duration_sec' => 30,
                'xp' => 10,
                'double_xp' => $index === 1,
            ]);
        }

        $session = LiveQuizSession::query()->create([
            'live_quiz_id' => $quiz->id,
            'teacher_user_id' => $teacher->id,
            'join_code' => 'SYNC01',
            'status' => 'live',
            'current_index' => 0,
            'is_locked' => false,
            'started_at_ms' => $this->nowMs(),
            'ends_at_ms' => $this->nowMs() + 30000,
        ]);

        foreach ([$fastStudent, $silentStudent] as $student) {
            LiveQuizParticipant::query()->create([
                'live_quiz_session_id' => $session->id,
                'student_user_id' => $student->id,
                'joined_at_ms' => $this->nowMs(),
            ]);
        }

        LiveQuizAnswer::query()->create([
            'live_quiz_session_id' => $session->id,
            'student_user_id' => $fastStudent->id,
            'question_index' => 0,
            'selected_answer' => 'A',
            'is_correct' => true,
            'xp_earned' => 10,
            'answered_at_ms' => $this->nowMs(),
        ]);

        $this->actingAs($teacher)
            ->post(route('live-quiz.session.next', $session))
            ->assertRedirect();

        $session->refresh();
        $this->assertTrue($session->is_locked);
        $this->assertSame(0, (int) $session->current_index);
        $this->assertDatabaseHas('live_quiz_answers', [
            'live_quiz_session_id' => $session->id,
            'student_user_id' => $silentStudent->id,
            'question_index' => 0,
            'selected_answer' => null,
            'is_correct' => false,
        ]);

        $this->actingAs($teacher)
            ->getJson(route('live-quiz.session.status', $session))
            ->assertOk()
            ->assertJsonPath('is_locked', true)
            ->assertJsonPath('top_five.0.student_name', 'Ayşe Yılmaz')
            ->assertJsonPath('top_five.0.is_correct', true)
            ->assertJsonCount(1, 'top_five');

        $session->update(['ends_at_ms' => $this->nowMs() - 1]);

        $this->actingAs($teacher)
            ->getJson(route('live-quiz.session.status', $session))
            ->assertOk()
            ->assertJsonPath('current_index', 1)
            ->assertJsonPath('phase', 'intro')
            ->assertJsonPath('is_locked', true);

        $this->actingAs($teacher)
            ->get(route('live-quiz.session.show', $session))
            ->assertOk()
            ->assertSee('2 Kat Puanlı Soru');
        $this->actingAs($fastStudent)
            ->get(route('student.live-quiz.play', $session))
            ->assertOk()
            ->assertSee('2 Kat Puanlı Soru');

        $session->refresh()->update(['ends_at_ms' => $this->nowMs() - 1]);

        $this->actingAs($fastStudent)
            ->getJson(route('student.live-quiz.status', $session))
            ->assertOk()
            ->assertJsonPath('current_index', 1)
            ->assertJsonPath('phase', 'question')
            ->assertJsonPath('is_locked', false);
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
