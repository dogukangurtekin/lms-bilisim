<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\TeacherGameAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveQuizActivityVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_live_quiz_in_assignable_game_list(): void
    {
        [$admin] = $this->users();

        $this->actingAs($admin)
            ->get('/etkinlikler')
            ->assertOk()
            ->assertSee('value="live-quiz"', false)
            ->assertSee('Canlı Quiz');
    }

    public function test_assigned_teacher_sees_live_quiz_with_the_correct_link(): void
    {
        [$admin, $teacherUser, $teacher] = $this->users();
        TeacherGameAssignment::query()->create([
            'teacher_id' => $teacher->id,
            'game_slug' => 'live-quiz',
            'game_name' => 'Canlı Quiz',
            'assigned_by' => $admin->id,
        ]);

        $this->actingAs($teacherUser)
            ->get('/etkinlikler')
            ->assertOk()
            ->assertSee('Canlı Quiz')
            ->assertSee(route('live-quiz.index'), false);
    }

    public function test_student_always_sees_live_quiz_join_card(): void
    {
        [, , $teacher, $studentUser] = $this->users();
        $class = SchoolClass::query()->create([
            'name' => '6',
            'section' => 'B',
            'grade_level' => 6,
            'teacher_id' => $teacher->id,
            'academic_year' => '2026-2027',
        ]);
        $studentUser->student()->update(['school_class_id' => $class->id]);

        $this->actingAs($studentUser)
            ->get('/etkinlikler')
            ->assertOk()
            ->assertSee('Canlı Quiz')
            ->assertSee(route('student.live-quiz.join.form'), false);
    }

    private function users(): array
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);

        $admin = User::query()->create([
            'role_id' => $adminRole->id,
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'secret123',
            'is_active' => true,
        ]);
        $teacherUser = User::query()->create([
            'role_id' => $teacherRole->id,
            'name' => 'Teacher',
            'email' => 'teacher@example.test',
            'password' => 'secret123',
            'is_active' => true,
        ]);
        $teacher = Teacher::query()->create([
            'user_id' => $teacherUser->id,
            'branch' => 'Bilisim Teknolojileri',
        ]);
        $studentUser = User::query()->create([
            'role_id' => $studentRole->id,
            'name' => 'Student',
            'email' => 'student@example.test',
            'password' => 'secret123',
            'is_active' => true,
        ]);

        return [$admin, $teacherUser, $teacher, $studentUser];
    }
}
