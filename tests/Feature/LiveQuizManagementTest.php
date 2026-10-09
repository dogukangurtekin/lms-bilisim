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

class LiveQuizManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_edit_close_and_delete_owned_quiz_sessions_and_reports(): void
    {
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $teacher = $this->user($teacherRole->id, 'Öğretmen', 'quiz-manage@test.local');
        $student = $this->user($studentRole->id, 'Öğrenci', 'quiz-manage-student@test.local');
        $quiz = $this->quiz($teacher);

        $this->actingAs($teacher)
            ->get(route('live-quiz.edit', $quiz))
            ->assertOk()
            ->assertSee('Quiz düzenleniyor')
            ->assertSee('Eski soru');

        $questions = [[
            'type' => 'multiple',
            'question' => 'Güncellenen soru',
            'durationSec' => 20,
            'xp' => 15,
            'doubleXp' => false,
            'options' => ['Bir', 'İki'],
            'correctIndex' => 1,
        ]];

        $this->actingAs($teacher)
            ->put(route('live-quiz.update', $quiz), [
                'title' => 'Güncellenen Quiz',
                'school_class_id' => '',
                'join_mode' => 'instant',
                'questions_json' => json_encode($questions, JSON_UNESCAPED_UNICODE),
            ])
            ->assertRedirect(route('live-quiz.index'));

        $this->assertDatabaseHas('live_quizzes', [
            'id' => $quiz->id,
            'title' => 'Güncellenen Quiz',
            'join_mode' => 'instant',
        ]);
        $this->assertDatabaseHas('live_quiz_questions', [
            'live_quiz_id' => $quiz->id,
            'question_text' => 'Güncellenen soru',
            'correct_answer' => 'B',
        ]);
        $this->assertSame(1, LiveQuizQuestion::query()->where('live_quiz_id', $quiz->id)->count());

        $finished = $this->createQuizSession($quiz, $teacher, 'OLD001', 'finished');
        $live = $this->createQuizSession($quiz, $teacher, 'LIVE01', 'live');

        $this->actingAs($teacher)
            ->get(route('live-quiz.index'))
            ->assertOk()
            ->assertSee('data-confirm=', false)
            ->assertDontSee('onsubmit=', false)
            ->assertSee('Oturumu Kapat')
            ->assertSee('Tüm Geçmiş Raporları Sil');

        LiveQuizParticipant::query()->create([
            'live_quiz_session_id' => $finished->id,
            'student_user_id' => $student->id,
            'joined_at_ms' => $this->nowMs(),
        ]);
        LiveQuizAnswer::query()->create([
            'live_quiz_session_id' => $finished->id,
            'student_user_id' => $student->id,
            'question_index' => 0,
            'selected_answer' => 'B',
            'is_correct' => true,
            'xp_earned' => 15,
            'answered_at_ms' => $this->nowMs(),
        ]);

        $this->actingAs($teacher)
            ->delete(route('live-quiz.sessions.history.destroy'))
            ->assertRedirect(route('live-quiz.index'));
        $this->assertDatabaseMissing('live_quiz_sessions', ['id' => $finished->id]);
        $this->assertDatabaseMissing('live_quiz_answers', ['live_quiz_session_id' => $finished->id]);
        $this->assertDatabaseHas('live_quiz_sessions', ['id' => $live->id, 'status' => 'live']);

        $this->actingAs($teacher)
            ->post(route('live-quiz.session.finish', $live))
            ->assertRedirect(route('live-quiz.index'));
        $this->assertDatabaseHas('live_quiz_sessions', ['id' => $live->id, 'status' => 'finished']);

        $this->actingAs($teacher)
            ->delete(route('live-quiz.session.destroy', $live))
            ->assertRedirect(route('live-quiz.index'));
        $this->assertDatabaseMissing('live_quiz_sessions', ['id' => $live->id]);

        $this->actingAs($teacher)
            ->delete(route('live-quiz.destroy', $quiz))
            ->assertRedirect(route('live-quiz.index'));
        $this->assertDatabaseMissing('live_quizzes', ['id' => $quiz->id]);
        $this->assertDatabaseMissing('live_quiz_questions', ['live_quiz_id' => $quiz->id]);
    }

    public function test_teacher_cannot_manage_another_teachers_quiz(): void
    {
        $role = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $owner = $this->user($role->id, 'Sahip', 'quiz-owner@test.local');
        $other = $this->user($role->id, 'Diğer', 'quiz-other@test.local');
        $quiz = $this->quiz($owner);

        $this->actingAs($other)->get(route('live-quiz.edit', $quiz))->assertForbidden();
        $this->actingAs($other)->delete(route('live-quiz.destroy', $quiz))->assertForbidden();
    }

    public function test_teacher_can_close_all_owned_active_sessions_without_closing_another_teachers_session(): void
    {
        $role = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $teacher = $this->user($role->id, 'Öğretmen', 'close-all-owner@test.local');
        $other = $this->user($role->id, 'Diğer Öğretmen', 'close-all-other@test.local');
        $quiz = $this->quiz($teacher);
        $otherQuiz = $this->quiz($other);

        $live = $this->createQuizSession($quiz, $teacher, 'CLS001', 'live');
        $lobby = $this->createQuizSession($quiz, $teacher, 'CLS002', 'lobby');
        $alreadyFinished = $this->createQuizSession($quiz, $teacher, 'CLS003', 'finished');
        $otherLive = $this->createQuizSession($otherQuiz, $other, 'CLS004', 'live');

        $this->actingAs($teacher)
            ->post(route('live-quiz.sessions.close-all'))
            ->assertRedirect(route('live-quiz.index'));

        foreach ([$live, $lobby] as $session) {
            $session->refresh();
            $this->assertSame('finished', $session->status);
            $this->assertTrue($session->is_locked);
            $this->assertNotNull($session->finished_at_ms);
        }
        $this->assertSame('finished', $alreadyFinished->fresh()->status);
        $this->assertSame('live', $otherLive->fresh()->status);

        $this->actingAs($teacher)
            ->get(route('live-quiz.index'))
            ->assertOk()
            ->assertSee('Tüm Oturumları Kapat');
    }

    private function quiz(User $teacher): LiveQuiz
    {
        $quiz = LiveQuiz::query()->create([
            'teacher_user_id' => $teacher->id,
            'title' => 'Eski Quiz',
            'join_mode' => 'code',
            'status' => 'active',
        ]);
        LiveQuizQuestion::query()->create([
            'live_quiz_id' => $quiz->id,
            'sort_order' => 0,
            'type' => 'multiple',
            'question_text' => 'Eski soru',
            'options' => ['A', 'B'],
            'correct_answer' => 'A',
            'duration_sec' => 30,
            'xp' => 10,
            'double_xp' => false,
        ]);

        return $quiz;
    }

    private function createQuizSession(LiveQuiz $quiz, User $teacher, string $code, string $status): LiveQuizSession
    {
        return LiveQuizSession::query()->create([
            'live_quiz_id' => $quiz->id,
            'teacher_user_id' => $teacher->id,
            'join_code' => $code,
            'status' => $status,
            'current_index' => 0,
            'is_locked' => $status === 'finished',
            'started_at_ms' => $this->nowMs(),
            'ends_at_ms' => $this->nowMs() + 30000,
            'finished_at_ms' => $status === 'finished' ? $this->nowMs() : null,
        ]);
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
