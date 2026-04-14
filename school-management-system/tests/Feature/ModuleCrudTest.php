<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModuleCrudTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);

        return User::create([
            'role_id' => $role->id,
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);
    }

    private function seedAcademicBase(): array
    {
        $teacherUser = User::create(['name' => 'Teacher User', 'email' => 'teacher@test.local', 'password' => Hash::make('secret123')]);
        $teacher = Teacher::create(['user_id' => $teacherUser->id, 'branch' => 'Math']);
        $class = SchoolClass::create(['name' => '10A', 'section' => 'A', 'grade_level' => 10, 'teacher_id' => $teacher->id, 'academic_year' => '2026-2027']);

        $studentUser = User::create(['name' => 'Student User', 'email' => 'student@test.local', 'password' => Hash::make('secret123')]);
        $student = Student::create(['user_id' => $studentUser->id, 'student_no' => 'S1001', 'school_class_id' => $class->id]);

        return compact('teacher', 'class', 'student', 'teacherUser', 'studentUser');
    }

    public function test_admin_can_access_module_indexes(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $routes = [
            '/students', '/teachers', '/classes', '/courses', '/grades', '/attendance', '/announcements', '/users', '/reports',
        ];

        foreach ($routes as $route) {
            $this->get($route)->assertStatus(200);
        }
    }

    public function test_admin_can_create_student(): void
    {
        $admin = $this->adminUser();
        $ctx = $this->seedAcademicBase();
        $extraUser = User::create(['name' => 'New Student User', 'email' => 'newstudent@test.local', 'password' => Hash::make('secret123')]);

        $this->actingAs($admin)
            ->post('/students', [
                'user_id' => $extraUser->id,
                'student_no' => 'S2001',
                'school_class_id' => $ctx['class']->id,
            ])
            ->assertRedirect('/students');

        $this->assertDatabaseHas('students', ['student_no' => 'S2001']);
    }

    public function test_reports_csv_download_works(): void
    {
        $admin = $this->adminUser();
        $this->seedAcademicBase();

        $this->actingAs($admin)
            ->get('/reports/students.csv')
            ->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
