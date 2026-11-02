<?php

namespace Tests\Feature;

use App\Models\CompetitionParticipant;
use App\Models\CompetitionRoom;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_shows_summary_charts_and_csv_for_any_game(): void
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $admin = User::query()->create(['role_id' => $adminRole->id, 'name' => 'Yönetici', 'email' => 'rp-admin@example.test', 'password' => 'secret123', 'is_active' => true]);
        $other = User::query()->create(['role_id' => $teacherRole->id, 'name' => 'Başka Öğretmen', 'email' => 'rp-other@example.test', 'password' => 'secret123', 'is_active' => true]);

        $room = CompetitionRoom::query()->create([
            'teacher_user_id' => $admin->id, 'game_slug' => 'block-grid-runner', 'game_name' => 'Blok Izgara',
            'level_from' => 1, 'level_to' => 3, 'duration_seconds' => 600, 'join_code' => 'RPT001',
            'status' => 'finished', 'started_at_ms' => 1000000, 'ends_at_ms' => 1600000, 'finished_at_ms' => 1600000,
        ]);
        foreach ([['Ayşe Yılmaz', 100, 90, 120000], ['Ali Kaya', 40, 30, null]] as $i => [$name, $pct, $xp, $fin]) {
            $u = User::query()->create(['role_id' => $studentRole->id, 'name' => $name, 'email' => 'rp-s'.$i.'@example.test', 'password' => 'secret123', 'is_active' => true]);
            CompetitionParticipant::query()->create([
                'competition_room_id' => $room->id, 'student_user_id' => $u->id, 'user_name' => $name,
                'progress_percent' => $pct, 'xp_earned' => $xp, 'joined_at_ms' => 1000000,
                'finished_at_ms' => $fin ? 1000000 + $fin : null,
            ]);
        }

        $this->actingAs($admin)->get(route('competitions.room.report', $room))
            ->assertOk()->assertSee('Yarışma Raporu')->assertSee('Ayşe Yılmaz')->assertSee('Ali Kaya')
            ->assertSee('XP Sıralaması')->assertSee('Tamamlama Durumu')->assertSee('2 dk');

        $csv = $this->actingAs($admin)->get(route('competitions.room.report.csv', $room));
        $csv->assertOk();
        $this->assertStringContainsString('Ayşe Yılmaz', $csv->streamedContent());

        $this->actingAs($other)->get(route('competitions.room.report', $room))->assertForbidden();
        $this->actingAs($admin)->get(route('competitions.room.show', $room))->assertOk()->assertSee('Rapor');
    }
}
