<?php

namespace Tests\Feature;

use App\Models\CompetitionRoom;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionRoomOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $teacherA;
    private User $teacherB;
    private CompetitionRoom $roomA;
    private CompetitionRoom $roomB;
    private CompetitionRoom $roomAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $ar = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $tr = Role::query()->firstOrCreate(['slug' => 'teacher'], ['name' => 'Teacher']);
        $this->admin = User::query()->create(['role_id' => $ar->id, 'name' => 'Yönetici', 'email' => 'ad@t.l', 'password' => 'secret123', 'is_active' => true]);
        $this->teacherA = User::query()->create(['role_id' => $tr->id, 'name' => 'Hoca A', 'email' => 'a@t.l', 'password' => 'secret123', 'is_active' => true]);
        $this->teacherB = User::query()->create(['role_id' => $tr->id, 'name' => 'Hoca B', 'email' => 'b@t.l', 'password' => 'secret123', 'is_active' => true]);
        $this->roomA = $this->room($this->teacherA, 'OyunA', 'AAAAAA');
        $this->roomB = $this->room($this->teacherB, 'OyunB', 'BBBBBB');
        $this->roomAdmin = $this->room($this->admin, 'OyunAdmin', 'CCCCCC');
    }

    private function room(User $owner, string $name, string $code): CompetitionRoom
    {
        return CompetitionRoom::query()->create([
            'teacher_user_id' => $owner->id, 'game_slug' => 'scratch-runner', 'game_name' => $name, 'level_from' => 1, 'level_to' => 1,
            'duration_seconds' => 600, 'join_code' => $code, 'is_open' => false, 'status' => 'lobby',
        ]);
    }

    public function test_everyone_sees_only_their_own_rooms_in_the_list(): void
    {
        $this->actingAs($this->admin)->get(route('competitions.index'))->assertOk()->assertSee('OyunAdmin')->assertDontSee('OyunA<', false)->assertDontSee('OyunB');
        $this->actingAs($this->teacherA)->get(route('competitions.index'))->assertOk()->assertSee('OyunA')->assertDontSee('OyunB')->assertDontSee('OyunAdmin');
        $this->actingAs($this->teacherB)->get(route('competitions.index'))->assertOk()->assertSee('OyunB')->assertDontSee('OyunAdmin');
    }

    public function test_rooms_of_others_cannot_be_opened_even_by_admin(): void
    {
        foreach ([$this->admin, $this->teacherB] as $other) {
            $this->actingAs($other)->get(route('competitions.room.show', $this->roomA))->assertForbidden();
            $this->actingAs($other)->get(route('competitions.room.status', $this->roomA))->assertForbidden();
            $this->actingAs($other)->get(route('competitions.room.report', $this->roomA))->assertForbidden();
            $this->actingAs($other)->delete(route('competitions.room.destroy', $this->roomA))->assertForbidden();
        }
        $this->actingAs($this->teacherA)->get(route('competitions.room.show', $this->roomA))->assertOk();
    }

    public function test_delete_all_removes_only_the_admins_own_rooms(): void
    {
        $this->actingAs($this->admin)->delete(route('competitions.rooms.destroy-all'))->assertRedirect();
        $this->assertNull(CompetitionRoom::query()->find($this->roomAdmin->id));
        $this->assertNotNull(CompetitionRoom::query()->find($this->roomA->id));
        $this->assertNotNull(CompetitionRoom::query()->find($this->roomB->id));
    }
}
