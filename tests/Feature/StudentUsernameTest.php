<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\StudentCredential;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentUsernameTest extends TestCase
{
    use RefreshDatabase;

    private function mk(int $roleId, string $name, string $email): User
    {
        return User::query()->create(['role_id' => $roleId, 'name' => $name, 'email' => $email, 'password' => 'secret123', 'is_active' => true]);
    }

    public function test_student_cannot_change_username_but_admin_can(): void
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $admin = $this->mk($adminRole->id, 'Yönetici', 'adm@school.local');
        $teacherUser = $this->mk($teacherRole->id, 'Öğretmen', 'ogr@school.local');
        $teacher = Teacher::query()->create(['user_id' => $teacherUser->id, 'branch' => 'Bilişim']);
        $class = SchoolClass::query()->create(['name' => '5', 'section' => 'A', 'grade_level' => 5, 'teacher_id' => $teacher->id, 'academic_year' => '2026-2027']);
        $studentUser = $this->mk($studentRole->id, 'Ali Veli', 'ali.veli@school.local');
        $other = $this->mk($studentRole->id, 'Başka Öğrenci', 'baska@school.local');
        $student = $studentUser->student()->firstOrFail();
        $student->update(['school_class_id' => $class->id, 'student_no' => '101']);
        StudentCredential::query()->create(['student_id' => $student->id, 'username' => 'ali.veli', 'plain_password' => 'secret123']);

        // 1) Öğrenci profilinden kullanıcı adını değiştirmeye çalışır: ad güncellenir, kullanıcı adı değişmez
        $this->actingAs($studentUser)->get(route('profile.edit'))->assertOk()->assertSee('yalnızca yönetici', false);
        $this->actingAs($studentUser)->put(route('profile.update'), [
            'first_name' => 'Ali', 'last_name' => 'Yeni', 'username' => 'hacker123',
        ])->assertSessionHasNoErrors();
        $studentUser->refresh();
        $this->assertSame('ali.veli@school.local', $studentUser->email);
        $this->assertSame('Ali Yeni', $studentUser->name);
        $this->assertSame('ali.veli', StudentCredential::query()->where('student_id', $student->id)->value('username'));

        // 2) Admin Öğrenciyi Düzenle'den kullanıcı adını günceller
        $payload = ['first_name' => 'Ali', 'last_name' => 'Yeni', 'student_no' => '101', 'school_class_id' => $class->id];
        $this->actingAs($admin)->get(route('students.edit', $student))->assertOk()->assertSee('name="username"', false);
        $this->actingAs($admin)->put(route('students.update', $student), $payload + ['username' => 'ali.yeni'])->assertRedirect();
        $this->assertSame('ali.yeni@school.local', $studentUser->fresh()->email);
        $this->assertSame('ali.yeni', StudentCredential::query()->where('student_id', $student->id)->value('username'));

        // 3) Aynı kullanıcı adı başka hesapta varsa reddedilir
        $this->actingAs($admin)->from(route('students.edit', $student))
            ->put(route('students.update', $student), $payload + ['username' => 'baska'])->assertSessionHasErrors('username');
        $this->assertSame('ali.yeni@school.local', $studentUser->fresh()->email);

        // 4) Öğretmen kullanıcı adını değiştiremez
        $this->actingAs($teacherUser)->put(route('students.update', $student), $payload + ['username' => 'ogretmen.degistirdi']);
        $this->assertSame('ali.yeni@school.local', $studentUser->fresh()->email);

        // 5) Kullanıcı yönetiminden (admin) e-posta değişince giriş kaydı eşitlenir
        $this->actingAs($admin)->put(route('users.update', $studentUser), ['name' => 'Ali Yeni', 'email' => 'ali.son@school.local'])->assertRedirect();
        $this->assertSame('ali.son', StudentCredential::query()->where('student_id', $student->id)->value('username'));
    }
}
