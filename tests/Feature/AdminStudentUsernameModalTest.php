<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\StudentCredential;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStudentUsernameModalTest extends TestCase
{
    use RefreshDatabase;

    private function mk(int $roleId, string $name, string $email): User
    {
        return User::query()->create(['role_id' => $roleId, 'name' => $name, 'email' => $email, 'password' => 'secret123', 'is_active' => true]);
    }

    public function test_admin_changes_username_from_login_modal_and_turkish_names_still_save(): void
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $admin = $this->mk($adminRole->id, 'Yonetici', 'adm@school.local');
        $tUser = $this->mk($teacherRole->id, 'Ogretmen', 'ogr@school.local');
        $teacher = Teacher::query()->create(['user_id' => $tUser->id, 'branch' => 'Bilisim']);
        $class = SchoolClass::query()->create(['name' => '5', 'section' => 'A', 'grade_level' => 5, 'teacher_id' => $teacher->id, 'academic_year' => '2026-2027']);
        $u = $this->mk($studentRole->id, 'Ada Görgülü', 'agörgülü.5a@school.local');
        $this->mk($studentRole->id, 'Baska', 'dolu.5a@school.local');
        $st = $u->student()->firstOrFail();
        $st->update(['school_class_id' => $class->id, 'student_no' => '501']);
        StudentCredential::query()->create(['student_id' => $st->id, 'username' => 'agörgülü.5a', 'plain_password' => 'secret123']);

        $base = ['first_name' => 'Ada', 'last_name' => 'Görgülü', 'student_no' => '501', 'school_class_id' => $class->id];

        // Türkçe harfli mevcut kullanıcı adı değişmeden kaydedilebilir
        $this->actingAs($admin)->put(route('students.update', $st), $base + ['username' => 'agörgülü.5a'])->assertSessionHasNoErrors();
        $this->assertSame('agörgülü.5a@school.local', $u->fresh()->email);

        // yeni kullanıcı adı: e-posta ve giriş kaydı güncellenir
        $this->actingAs($admin)->put(route('students.update', $st), $base + ['username' => 'ada.yeni'])->assertSessionHasNoErrors();
        $this->assertSame('ada.yeni@school.local', $u->fresh()->email);
        $this->assertSame('ada.yeni', StudentCredential::query()->where('student_id', $st->id)->value('username'));

        // başkasında olan kullanıcı adı reddedilir
        $this->actingAs($admin)->put(route('students.update', $st), $base + ['username' => 'dolu.5a'])->assertSessionHasErrors('username');
        $this->assertSame('ada.yeni@school.local', $u->fresh()->email);
    }
}
