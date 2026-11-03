<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivitiesAssignDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_game_picker_as_dropdown_with_same_form_fields(): void
    {
        $role = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $admin = User::query()->create(['role_id' => $role->id, 'name' => 'Yönetici', 'email' => 'ad@t.l', 'password' => 'secret123', 'is_active' => true]);

        $this->actingAs($admin)->get(route('activities.index'))
            ->assertOk()
            ->assertSee('id="act-dd"', false)
            ->assertSee('name="game_slugs[]"', false)
            ->assertSee('id="activities-select-all"', false)
            ->assertSee('id="activity-teacher-select"', false);
    }
}
