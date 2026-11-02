<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\ScratchProject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\StudentProgressReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScratchReportTest extends TestCase
{
    use RefreshDatabase;

    private function user(int $roleId, string $name, string $email): User
    {
        return User::query()->create(['role_id' => $roleId, 'name' => $name, 'email' => $email, 'password' => 'secret123', 'is_active' => true]);
    }

    private function projectJson(): string
    {
        return json_encode([
            'v' => 1, 'name' => 'Oyunum', 'vars' => ['puan' => 0, 'can' => 3],
            'sprites' => [
                ['id' => 1, 'name' => 'Kedi', 'scripts' => [['x' => 0, 'y' => 0, 'blocks' => [['op' => 'whenflag', 'args' => []], ['op' => 'move', 'args' => [10]], ['op' => 'repeat', 'args' => [3, [['op' => 'turn', 'args' => [15]]]]]]]]],
                ['id' => 2, 'name' => 'Roket', 'scripts' => [['x' => 0, 'y' => 0, 'blocks' => [['op' => 'whenkey', 'args' => ['a']], ['op' => 'move', 'args' => [5]]]]]],
            ],
        ]);
    }

    public function test_scratch_time_accumulates_and_shows_in_progress_report(): void
    {
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $teacherUser = $this->user($teacherRole->id, 'Öğretmen', 'sc-teacher@example.test');
        $studentUser = $this->user($studentRole->id, 'Öğrenci', 'sc-student@example.test');
        $teacher = Teacher::query()->create(['user_id' => $teacherUser->id, 'branch' => 'Bilişim']);
        $class = SchoolClass::query()->create(['name' => '5', 'section' => 'A', 'grade_level' => 5, 'teacher_id' => $teacher->id, 'academic_year' => '2026-2027']);
        $student = $studentUser->student()->firstOrFail();
        $student->update(['school_class_id' => $class->id]);

        $payload = ['name' => 'Oyunum', 'data' => $this->projectJson(), 'client_ts' => 1, 'secs' => 120];
        $this->actingAs($studentUser)->postJson('/scratch-api/projeler', $payload)->assertOk();
        $this->actingAs($studentUser)->postJson('/scratch-api/projeler', ['secs' => 90] + $payload)->assertOk();
        $this->actingAs($studentUser)->postJson('/scratch-api/projeler', ['secs' => 99999] + $payload)->assertStatus(422);

        $this->assertSame(210, (int) ScratchProject::query()->firstOrFail()->active_seconds);

        $sc = app(StudentProgressReportService::class)->build($student)['scratch'];
        $this->assertSame(1, $sc['project_count']);
        $this->assertSame(2, $sc['sprites']);
        $this->assertSame(2, $sc['scripts']);
        $this->assertSame(6, $sc['blocks']);
        $this->assertSame(5, $sc['block_types']);
        $this->assertSame(2, $sc['variables']);
        $this->assertSame(210, $sc['seconds']);
        $this->assertSame('3 dk', $sc['time_text']);

        $this->actingAs($studentUser)->get(route('student.portal.progress-report'))
            ->assertOk()->assertSee('Blok Kodlama Stüdyosu')->assertSee('Oyunum');
        $this->actingAs($studentUser)->get(route('student.portal.progress'))
            ->assertOk()->assertSee('Blok Kodlama')->assertSee('6 blok');
    }
}
