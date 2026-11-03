<?php

namespace Tests\Feature;

use App\Models\CompetitionRoom;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTableContrastTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_result_table_is_transparent_so_light_text_stays_readable_on_the_dark_panel(): void
    {
        $role = Role::query()->firstOrCreate(['slug' => 'teacher'], ['name' => 'Teacher']);
        $teacher = User::query()->create(['role_id' => $role->id, 'name' => 'Hoca', 'email' => 'h@t.l', 'password' => 'secret123', 'is_active' => true]);
        $room = CompetitionRoom::query()->create([
            'teacher_user_id' => $teacher->id, 'game_slug' => 'scratch-runner', 'game_name' => 'Blok Kodlama', 'level_from' => 1, 'level_to' => 1,
            'duration_seconds' => 600, 'join_code' => 'RPT001', 'is_open' => false, 'status' => 'finished',
        ]);

        // Genel "table{background:#fff}" kuralı bu tabloyu beyaza boyuyordu; açık renk metin okunmuyordu.
        $this->actingAs($teacher)->get(route('competitions.room.report', $room))
            ->assertOk()
            ->assertSee('.rp-table{width:100%;border-collapse:collapse;font-size:14px;background:transparent}', false);
    }
}
