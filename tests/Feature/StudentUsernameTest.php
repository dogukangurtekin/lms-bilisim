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

    public function test_student_username_cannot_be_changed_by_anyone_including_admin(): void
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $admin = $this->mk($adminRole->id, 'Yönetici', 'adm@school.local');
        $teacherUser = $this->mk($teacherRole->id, 'Öğretmen', 'ogr@school.local');
        $teacher = Teacher::query()->create(['user_id' => $teacherUser->id, 'branch' => 'Bilişim']);
        $class = SchoolClass::query()->create(['name' => '5', 'section' => 'A', 'grade_level' => 5, 'teacher_id' => $teacher->id, 'academic_year' => '2026-2027']);
        $studentUser = $this->mk($studentRole->id, 'Ali Veli', 'ali.veli@school.local');
        $student = $studentUser->student()->firstOrFail();
        $student->update(['school_class_id' => $class->id, 'student_no' => '101']);
        StudentCredential::query()->create(['student_id' => $student->id, 'username' => 'ali.veli', 'plain_password' => 'secret123']);

        // 1) Öğrenci profilinden denerse: ad güncellenir, kullanıcı adı değişmez
        $this->actingAs($studentUser)->get(route('profile.edit'))->assertOk()->assertSee('yalnızca yönetici', false);
        $this->actingAs($studentUser)->put(route('profile.update'), ['first_name' => 'Ali', 'last_name' => 'Yeni', 'username' => 'hacker123'])->assertSessionHasNoErrors();
        $this->assertSame('ali.veli@school.local', $studentUser->fresh()->email);
        $this->assertSame('Ali Yeni', $studentUser->fresh()->name);

        // 2) Admin Öğrenciyi Düzenle'den denerse: kullanıcı adı değişmez, ekranda kilitli gösterilir
        $payload = ['first_name' => 'Ali', 'last_name' => 'Yeni', 'student_no' => '101', 'school_class_id' => $class->id];
        $this->actingAs($admin)->get(route('students.edit', $student))->assertOk()->assertSee('hicbir sekilde degistirilemez', false)->assertDontSee('name="username"', false);
        $this->actingAs($admin)->put(route('students.update', $student), $payload + ['username' => 'ali.yeni'])->assertRedirect();
        $this->assertSame('ali.veli@school.local', $studentUser->fresh()->email);
        $this->assertSame('ali.veli', StudentCredential::query()->where('student_id', $student->id)->value('username'));

        // 3) Öğretmen de değiştiremez
        $this->actingAs($teacherUser)->put(route('students.update', $student), $payload + ['username' => 'ogretmen.degistirdi']);
        $this->assertSame('ali.veli@school.local', $studentUser->fresh()->email);

        // 4) Kullanıcı yönetimi güncellemesi öğrencinin e-postasını (kullanıcı adı) değiştirmez; adı güncellenebilir
        $this->actingAs($admin)->put(route('users.update', $studentUser), ['name' => 'Ali Veli Son', 'email' => 'ali.son@school.local'])->assertRedirect();
        $this->assertSame('ali.veli@school.local', $studentUser->fresh()->email);
        $this->assertSame('Ali Veli Son', $studentUser->fresh()->name);
    }
}
