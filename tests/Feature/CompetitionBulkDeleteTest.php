<?php

namespace Tests\Feature;

use App\Models\CompetitionParticipant;
use App\Models\CompetitionRoom;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionBulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_remove_all_competition_sessions_without_deleting_student_history(): void
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $admin = $this->user($adminRole->id, 'Admin', 'bulk-admin@example.test');
        $teacher = $this->user($teacherRole->id, 'Öğretmen', 'bulk-teacher@example.test');
        $student = $this->user($studentRole->id, 'Öğrenci', 'bulk-student@example.test');

        $firstRoom = $this->room($admin, 'BULK01');
        $secondRoom = $this->room($teacher, 'BULK02');
        CompetitionParticipant::query()->create([
            'competition_room_id' => $firstRoom->id,
            'student_user_id' => $student->id,
            'user_name' => $student->name,
            'joined_at_ms' => 1,
            'finished_at_ms' => 100,
            'progress_percent' => 100,
            'xp_earned' => 25,
        ]);
        CompetitionParticipant::query()->create([
            'competition_room_id' => $secondRoom->id,
            'student_user_id' => $student->id,
            'user_name' => $student->name,
            'joined_at_ms' => 2,
            'finished_at_ms' => 200,
            'progress_percent' => 100,
            'xp_earned' => 35,
        ]);

        $this->actingAs($admin)
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertSee('Tüm Oturumları Sil');

        $this->actingAs($admin)
            ->delete(route('competitions.rooms.destroy-all'))
            ->assertRedirect(route('competitions.index'))
            ->assertSessionHas('ok');

        $this->assertSame(0, CompetitionRoom::query()->count());
        $this->assertSame(2, CompetitionRoom::withTrashed()->count());
        $this->assertDatabaseCount('competition_participants', 2);
        $this->assertSoftDeleted($firstRoom);
        $this->assertSoftDeleted($secondRoom);
    }

    public function test_teacher_cannot_bulk_delete_competition_sessions(): void
    {
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $teacher = $this->user($teacherRole->id, 'Öğretmen', 'bulk-forbidden@example.test');
        $this->room($teacher, 'BULK03');

        $this->actingAs($teacher)
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertDontSee('Tüm Oturumları Sil');

        $this->actingAs($teacher)
            ->delete(route('competitions.rooms.destroy-all'))
            ->assertForbidden();

        $this->assertDatabaseCount('competition_rooms', 1);
    }

    private function room(User $owner, string $joinCode): CompetitionRoom
    {
        return CompetitionRoom::query()->create([
            'teacher_user_id' => $owner->id,
            'game_slug' => 'compute-it-runner',
            'game_name' => 'Compute It',
            'level_from' => 1,
            'level_to' => 3,
            'duration_seconds' => 300,
            'join_code' => $joinCode,
            'status' => 'finished',
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
}
