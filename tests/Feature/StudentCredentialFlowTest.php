<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentCredential;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentCredentialFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_password_change_updates_login_and_printable_credential(): void
    {
        $student = $this->createStudent('eski.kullanici', 'Eski123');

        $this->actingAs($student->user)
            ->put('/profilim', [
                'first_name' => 'Ayse',
                'last_name' => 'Yilmaz',
                'username' => 'ayse.yilmaz',
                'password' => 'Yeni456',
                'password_confirmation' => 'Yeni456',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $student->user->refresh();
        $credential = StudentCredential::query()->where('student_id', $student->id)->firstOrFail();

        $this->assertTrue(Hash::check('Yeni456', $student->user->password));
        $this->assertSame('ayse.yilmaz@school.local', $student->user->email);
        $this->assertSame('ayse.yilmaz', $credential->username);
        $this->assertSame('Yeni456', $credential->plain_password);
        $this->assertGreaterThan(120, strlen((string) $credential->getRawOriginal('plain_password')));
    }

    public function test_login_cards_fill_missing_student_credentials_and_show_them(): void
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $admin = User::query()->create([
            'role_id' => $adminRole->id,
            'name' => 'Admin',
            'email' => 'admin@school.local',
            'password' => 'Admin123',
            'is_active' => true,
        ]);
        $student = $this->createStudent('kart.ogrenci', 'Eski123', createCredential: false);

        $response = $this->actingAs($admin)->get('/ogrenci-verileri/giris-kartlari');

        $response->assertOk()->assertSee('kart.ogrenci');

        $credential = StudentCredential::query()->where('student_id', $student->id)->firstOrFail();
        $this->assertNotSame('', $credential->plain_password);
        $response->assertSee($credential->plain_password);

        $student->user->refresh();
        $this->assertTrue(Hash::check($credential->plain_password, $student->user->password));
    }

    private function createStudent(string $username, string $password, bool $createCredential = true): Student
    {
        $role = Role::query()->firstOrCreate(['slug' => 'student'], ['name' => 'Student']);
        $class = SchoolClass::query()->create([
            'name' => '5',
            'section' => 'A',
            'grade_level' => 5,
            'academic_year' => '2026-2027',
        ]);
        $user = User::query()->create([
            'role_id' => $role->id,
            'name' => 'Ayse Yilmaz',
            'email' => $username.'@school.local',
            'password' => $password,
            'is_active' => true,
        ]);
        $student = Student::query()->where('user_id', $user->id)->firstOrFail();
        $student->update([
            'student_no' => 'S'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
            'school_class_id' => $class->id,
        ]);

        if ($createCredential) {
            StudentCredential::query()->create([
                'student_id' => $student->id,
                'username' => $username,
                'plain_password' => $password,
            ]);
        }

        return $student->load('user');
    }
}
