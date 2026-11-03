<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentMessage;
use App\Models\Teacher;
use App\Models\User;
use App\Support\StudentMessageSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentMessageToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        @unlink(storage_path('app/student-message.json'));
        parent::tearDown();
    }

    private function mk(int $grade): array
    {
        $ar = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $tr = Role::query()->firstOrCreate(['slug' => 'teacher'], ['name' => 'Teacher']);
        $sr = Role::query()->firstOrCreate(['slug' => 'student'], ['name' => 'Student']);
        $admin = User::query()->create(['role_id' => $ar->id, 'name' => 'Yönetici', 'email' => "a$grade@t.l", 'password' => 'secret123', 'is_active' => true]);
        $tu = User::query()->create(['role_id' => $tr->id, 'name' => 'Öğretmen', 'email' => "t$grade@t.l", 'password' => 'secret123', 'is_active' => true]);
        $teacher = Teacher::query()->create(['user_id' => $tu->id]);
        $class = SchoolClass::query()->create(['name' => "$grade-A", 'section' => 'A', 'grade_level' => $grade, 'teacher_id' => $teacher->id, 'academic_year' => '2026-2027']);
        $su = User::query()->create(['role_id' => $sr->id, 'name' => 'Öğrenci', 'email' => "s$grade@t.l", 'password' => 'secret123', 'is_active' => true]);
        Student::query()->updateOrCreate(['user_id' => $su->id], ['student_no' => "S$grade", 'school_class_id' => $class->id]);

        return [$admin, $su];
    }

    public function test_admin_closes_teacher_messages_for_grades_3_and_4_only(): void
    {
        [$admin, $s3] = $this->mk(3);
        $this->actingAs($admin)->post(route('profile.student-messages.update'), [])->assertRedirect();
        $this->assertFalse(StudentMessageSetting::g34TeacherOpen());

        $this->actingAs($s3)->post(route('student.messages.send'), ['recipient_type' => 'teacher', 'body' => 'Merhaba hocam'])
            ->assertSessionHas('error');
        $this->assertSame(0, StudentMessage::query()->count());

        $this->actingAs($s3)->post(route('student.messages.send'), ['recipient_type' => 'admin', 'body' => 'Merhaba yönetici'])
            ->assertSessionHas('ok');
        $this->assertSame(1, StudentMessage::query()->count());
    }

    public function test_other_grades_unaffected_and_reopen_works(): void
    {
        [$admin, $s5] = $this->mk(5);
        $this->actingAs($admin)->post(route('profile.student-messages.update'), [])->assertRedirect();
        $this->actingAs($s5)->post(route('student.messages.send'), ['recipient_type' => 'teacher', 'body' => 'Merhaba hocam'])->assertSessionHas('ok');

        $this->actingAs($admin)->post(route('profile.student-messages.update'), ['g34_teacher' => '1'])->assertRedirect();
        $this->assertTrue(StudentMessageSetting::g34TeacherOpen());
    }

    public function test_non_admin_cannot_change_setting(): void
    {
        [, $s3] = $this->mk(3);
        $this->actingAs($s3)->post(route('profile.student-messages.update'), [])->assertForbidden();
    }
}
