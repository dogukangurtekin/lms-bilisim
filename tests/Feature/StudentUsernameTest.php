<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\StudentCredential;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentUsernameTest extends TestCase
{
    use RefreshDatabase;

    private function mk(int $roleId, string $name, string $email): User
    {
        return User::query()->create(['role_id' => $roleId, 'name' => $name, 'email' => $email, 'password' => 'secret123', 'is_active' => true]);
    }

    public function test_only_admin_can_update_student_and_teacher_username_name_and_password(): void
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
        $studentUser->forceFill(['password_change_required' => true])->save();
        StudentCredential::query()->create(['student_id' => $student->id, 'username' => 'ali.veli', 'plain_password' => 'secret123']);

        // 1) Öğrenci kendi kullanıcı adını değiştiremez
        $this->actingAs($studentUser->fresh()->forceFill(['password_change_required' => false]))->put(route('profile.update'), ['first_name' => 'Ali', 'last_name' => 'Yeni', 'username' => 'hacker123'])->assertSessionHasNoErrors();
        $this->assertSame('ali.veli@school.local', $studentUser->fresh()->email);
        User::query()->whereKey($studentUser->id)->update(['password_change_required' => true]);   // 1. adım bayrağı kaldırdı; DB'de yeniden zorunlu yap

        // 2) Admin: kullanıcı listesinde öğrenci için Düzenle düğmesi var
        $this->actingAs($admin)->get(route('users.index'))->assertOk()->assertSee('edit-user-trigger', false)->assertSee('Kullanıcı Adı');

        // 3) Admin: yalnızca kullanıcı adı yazarak öğrenciyi günceller (alan adı korunur), şifreyi de değiştirir
        $this->actingAs($admin)->put(route('users.update', $studentUser), ['name' => 'Ali Veli Son', 'email' => 'Ali.Son', 'password' => 'Yeni9999'])->assertRedirect();
        $fresh = $studentUser->fresh();
        $this->assertSame('ali.son@school.local', $fresh->email);
        $this->assertSame('Ali Veli Son', $fresh->name);
        $this->assertTrue(Hash::check('Yeni9999', $fresh->password));
        $cred = StudentCredential::query()->where('student_id', $student->id)->firstOrFail();
        $this->assertSame('ali.son', $cred->username);
        $this->assertSame('Yeni9999', $cred->plain_password);
        $this->assertTrue((bool) $fresh->password_change_required, 'Admin şifre vermesi öğrencinin zorunlu şifre ekranını kaldırmaz');

        // 4) Admin: öğretmenin kullanıcı adını ve şifresini günceller
        $this->actingAs($admin)->put(route('users.update', $teacherUser), ['name' => 'Öğretmen Yeni', 'email' => 'ogr.yeni', 'password' => 'Ogr12345'])->assertRedirect();
        $this->assertSame('ogr.yeni@school.local', $teacherUser->fresh()->email);
        $this->assertTrue(Hash::check('Ogr12345', $teacherUser->fresh()->password));

        // 5) Aynı kullanıcı adı başka hesapta varsa reddedilir
        $this->actingAs($admin)->put(route('users.update', $studentUser), ['name' => 'Ali Veli Son', 'email' => 'baska'])->assertRedirect()->assertSessionHas('err');
        $this->assertSame('ali.son@school.local', $studentUser->fresh()->email);

        // 6) Öğrenciyi Düzenle sayfası: admin kullanıcı adını değiştirir, öğretmen değiştiremez
        $payload = ['first_name' => 'Ali', 'last_name' => 'Son', 'student_no' => '101', 'school_class_id' => $class->id];
        $this->actingAs($admin)->get(route('students.edit', $student))->assertOk()->assertSee('name="username"', false);
        $this->actingAs($admin)->put(route('students.update', $student), $payload + ['username' => 'ali.final'])->assertRedirect();
        $this->assertSame('ali.final@school.local', $studentUser->fresh()->email);
        $this->assertSame('ali.final', StudentCredential::query()->where('student_id', $student->id)->value('username'));
        $this->actingAs($teacherUser->fresh())->put(route('students.update', $student), $payload + ['username' => 'ogretmen.degistirdi']);
        $this->assertSame('ali.final@school.local', $studentUser->fresh()->email);
    }
}
