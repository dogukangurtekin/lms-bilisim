<?php

namespace Tests\Feature;

use App\Models\LiveQuiz;
use App\Models\LiveQuizParticipant;
use App\Models\LiveQuizSession;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveQuizParticipantListTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_and_students_receive_the_live_participant_names(): void
    {
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $teacher = $this->user($teacherRole->id, 'Öğretmen', 'teacher@test.local');
        $student = $this->user($studentRole->id, 'Ayşe Yılmaz', 'ayse@test.local');
        $classmate = $this->user($studentRole->id, 'Mehmet Kaya', 'mehmet@test.local');

        $quiz = LiveQuiz::query()->create([
            'teacher_user_id' => $teacher->id,
            'title' => 'Canlı Deneme',
            'join_mode' => 'code',
            'status' => 'active',
        ]);
        $session = LiveQuizSession::query()->create([
            'live_quiz_id' => $quiz->id,
            'teacher_user_id' => $teacher->id,
            'join_code' => 'ABC123',
            'status' => 'lobby',
            'current_index' => 0,
            'is_locked' => true,
        ]);

        foreach ([$student, $classmate] as $participant) {
            LiveQuizParticipant::query()->create([
                'live_quiz_session_id' => $session->id,
                'student_user_id' => $participant->id,
                'joined_at_ms' => 1000,
            ]);
        }

        $this->actingAs($teacher)
            ->get(route('live-quiz.session.show', $session))
            ->assertOk()
            ->assertSee('Ayşe Yılmaz')
            ->assertSee('Mehmet Kaya');

        $this->actingAs($teacher)
            ->getJson(route('live-quiz.session.status', $session))
            ->assertOk()
            ->assertJsonPath('participants.0.student_name', 'Ayşe Yılmaz')
            ->assertJsonPath('participants.1.student_name', 'Mehmet Kaya');

        $this->actingAs($student)
            ->get(route('student.live-quiz.play', $session))
            ->assertOk()
            ->assertSee('Ayşe Yılmaz')
            ->assertSee('Mehmet Kaya');

        $this->actingAs($student)
            ->getJson(route('student.live-quiz.status', $session))
            ->assertOk()
            ->assertJsonPath('participants.0.student_name', 'Ayşe Yılmaz')
            ->assertJsonPath('participants.1.student_name', 'Mehmet Kaya');
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
}
