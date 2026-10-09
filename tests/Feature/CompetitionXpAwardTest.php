<?php

namespace Tests\Feature;

use App\Models\CompetitionParticipant;
use App\Models\CompetitionRoom;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionXpAwardTest extends TestCase
{
    use RefreshDatabase;

    public function test_competition_xp_is_added_to_profile_only_by_the_earned_difference(): void
    {
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $teacher = $this->user($teacherRole->id, 'Öğretmen', 'competition-teacher@test.local');
        $student = $this->user($studentRole->id, 'Öğrenci', 'competition-student@test.local');

        $room = CompetitionRoom::query()->create([
            'teacher_user_id' => $teacher->id,
            'game_slug' => 'compute-it-runner',
            'game_name' => 'Compute It',
            'level_from' => 1,
            'level_to' => 3,
            'duration_seconds' => 300,
            'join_code' => 'XPTEST',
            'status' => 'live',
            'started_at_ms' => $this->nowMs(),
            'ends_at_ms' => $this->nowMs() + 300000,
        ]);

        CompetitionParticipant::query()->create([
            'competition_room_id' => $room->id,
            'student_user_id' => $student->id,
            'user_name' => $student->name,
            'joined_at_ms' => $this->nowMs(),
        ]);
        UserProfile::query()->create([
            'user_id' => $student->id,
            'role' => 'student',
            'xp' => 10,
        ]);

        $this->actingAs($student)
            ->postJson(route('student.competitions.progress', $room), [
                'progress_percent' => 33,
                'current_level_index' => 1,
                'xp' => 40,
            ])
            ->assertOk();

        $this->assertSame(40, (int) CompetitionParticipant::query()->firstOrFail()->xp_earned);
        $this->assertSame(50, (int) UserProfile::query()->findOrFail($student->id)->xp);

        // Aynı oyun olayı tekrar gönderilirse XP ikinci kez eklenmemeli.
        $this->actingAs($student)
            ->postJson(route('student.competitions.progress', $room), ['xp' => 40])
            ->assertOk();
        $this->assertSame(50, (int) UserProfile::query()->findOrFail($student->id)->xp);

        // Kümülatif yarışma XP'si 70 olduğunda yalnızca yeni kazanılan 30 eklenir.
        $this->actingAs($student)
            ->postJson(route('student.competitions.progress', $room), ['xp' => 70])
            ->assertOk();
        $this->assertSame(70, (int) CompetitionParticipant::query()->firstOrFail()->xp_earned);
        $this->assertSame(80, (int) UserProfile::query()->findOrFail($student->id)->xp);
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
