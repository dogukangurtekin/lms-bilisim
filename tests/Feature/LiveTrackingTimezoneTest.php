<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveTrackingTimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_tracking_uses_istanbul_time_before_and_after_refresh(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-09 15:00:00', 'UTC'));

        try {
            $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
            $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);

            $admin = User::query()->create([
                'role_id' => $adminRole->id,
                'name' => 'Admin',
                'email' => 'tracking-admin@example.test',
                'password' => 'secret123',
                'is_active' => true,
            ]);
            $studentUser = User::query()->create([
                'role_id' => $studentRole->id,
                'name' => 'Takip Öğrencisi',
                'email' => 'tracking-student@example.test',
                'password' => 'secret123',
                'is_active' => true,
            ]);
            $class = SchoolClass::query()->create([
                'name' => '7',
                'section' => 'B',
                'grade_level' => 7,
                'academic_year' => '2026-2027',
            ]);
            $student = Student::query()->where('user_id', $studentUser->id)->firstOrFail();
            $student->update([
                'student_no' => 'IST-001',
                'school_class_id' => $class->id,
            ]);

            foreach ([
                ['2026-10-09 09:15:00', 'Giriş yaptı'],
                ['2026-10-09 10:30:00', 'Dersi açtı'],
            ] as [$loggedAt, $label]) {
                StudentActivityLog::query()->create([
                    'student_id' => $student->id,
                    'url' => '/dersler',
                    'action_label' => $label,
                    'logged_at' => $loggedAt,
                ]);
            }

            $this->actingAs($admin)
                ->get(route('live-tracking.index'))
                ->assertOk()
                ->assertSee('12:15');

            $this->actingAs($admin)
                ->getJson(route('live-tracking.refresh'))
                ->assertOk()
                ->assertJsonPath('0.first_seen', '12:15')
                ->assertJsonPath('0.log_count', 2);

            $this->actingAs($admin)
                ->get(route('live-tracking.show', $student))
                ->assertOk()
                ->assertSee('09.10.2026 13:30:00');
        } finally {
            Carbon::setTestNow();
        }
    }
}
