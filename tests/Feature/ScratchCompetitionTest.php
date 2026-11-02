<?php

namespace Tests\Feature;

use App\Models\CompetitionRoom;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScratchCompetitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_scratch_room_without_level_range(): void
    {
        $role = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $admin = User::query()->create(['role_id' => $role->id, 'name' => 'Yönetici', 'email' => 'sc-comp@example.test', 'password' => 'secret123', 'is_active' => true]);

        $this->actingAs($admin)->get(route('competitions.index'))
            ->assertOk()->assertSee('scratch-runner', false);

        $this->actingAs($admin)->post(route('competitions.store'), ['game_slug' => 'scratch-runner', 'school_class_id' => '', 'duration_minutes' => 15])
            ->assertRedirect();

        $room = CompetitionRoom::query()->firstOrFail();
        $this->assertSame('scratch-runner', $room->game_slug);
        $this->assertSame(1, (int) $room->level_from);
        $this->assertSame(1, (int) $room->level_to);
        $this->assertSame(900, (int) $room->duration_seconds);
    }
}
