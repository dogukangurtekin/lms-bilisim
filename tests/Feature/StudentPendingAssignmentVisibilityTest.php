<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseHomework;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPendingAssignmentVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_pending_lessons_and_homeworks_are_visually_prominent(): void
    {
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $teacherUser = $this->user($teacherRole->id, 'Öğretmen', 'pending-teacher@example.test');
        $studentUser = $this->user($studentRole->id, 'Öğrenci', 'pending-student@example.test');
        $teacher = Teacher::query()->create(['user_id' => $teacherUser->id, 'branch' => 'Bilişim']);
        $class = SchoolClass::query()->create([
            'name' => '7',
            'section' => 'B',
            'grade_level' => 7,
            'teacher_id' => $teacher->id,
            'academic_year' => '2026-2027',
        ]);
        $studentUser->student()->update(['school_class_id' => $class->id]);

        $course = Course::query()->create([
            'name' => 'Yeni Robotik Dersi',
            'code' => 'PENDING-LESSON',
            'teacher_id' => $teacher->id,
            'school_class_id' => $class->id,
            'weekly_hours' => 2,
        ]);
        CourseHomework::query()->create([
            'course_id' => $course->id,
            'school_class_id' => $class->id,
            'assignment_type' => 'lesson',
            'title' => 'Robotik Ders Ataması',
            'created_by' => $teacherUser->id,
        ]);
        CourseHomework::query()->create([
            'course_id' => $course->id,
            'school_class_id' => $class->id,
            'assignment_type' => 'homework',
            'title' => 'Yeni Algoritma Ödevi',
            'created_by' => $teacherUser->id,
        ]);

        $this->actingAs($studentUser)
            ->get(route('student.portal.assignments'))
            ->assertOk()
            ->assertSee('Yeni verilen ders')
            ->assertSee('Yeni verilen ödev')
            ->assertSee('assignment-status--pending', false)
            ->assertSee('assignment-pending', false);

        $this->actingAs($studentUser)
            ->get(route('student.portal.courses'))
            ->assertOk()
            ->assertSee('YENİ DERS')
            ->assertSee('BEKLİYOR');
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
