<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\StudentCredential;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private function mk(string $slug, string $name, string $email, bool $force = false): User
    {
        $role = Role::query()->firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);
        $u = User::query()->create(['role_id' => $role->id, 'name' => $name, 'email' => $email, 'password' => 'Eski123', 'is_active' => true]);
        if ($force) {
            $u->forceFill(['password_change_required' => true])->save();
        }

        return $u->fresh();
    }

    public function test_student_is_locked_to_the_password_screen_until_the_password_is_changed_once(): void
    {
        $student = $this->mk('student', 'Ali Veli', 'ali.veli@school.local', true);
        StudentCredential::query()->create(['student_id' => $student->student()->firstOrFail()->id, 'username' => 'ali.veli', 'plain_password' => 'Eski123']);

        // Hiçbir sayfa açılmaz, hepsi şifre ekranına yönlenir
        foreach (['dashboard', 'profile.edit', 'student.portal.courses', 'student.portal.assignments'] as $route) {
            $this->actingAs($student)->get(route($route))->assertRedirect(route('password.force.show'));
        }
        $this->actingAs($student)->getJson(route('dashboard'))->assertStatus(423)->assertJsonPath('redirect', route('password.force.show'));
        $this->actingAs($student)->get(route('password.force.show'))->assertOk()->assertSee('Şifreni güncelle')->assertSee('Ali Veli');

        // Geçersiz şifreler
        $post = fn (array $d) => $this->actingAs($student)->from(route('password.force.show'))->post(route('password.force.update'), $d);
        $post(['password' => 'abc', 'password_confirmation' => 'abc'])->assertSessionHasErrors('password');
        $post(['password' => 'Yeni4567', 'password_confirmation' => 'baska'])->assertSessionHasErrors('password');
        $post(['password' => 'Eski123', 'password_confirmation' => 'Eski123'])->assertSessionHasErrors('password');
        $post(['password' => '123456', 'password_confirmation' => '123456'])->assertSessionHasErrors('password');
        $post(['password' => 'ali.veli', 'password_confirmation' => 'ali.veli'])->assertSessionHasErrors('password');
        $this->assertTrue($student->fresh()->password_change_required);

        // Geçerli şifre: bayrak kalkar, şifre ve giriş kartı kaydı güncellenir
        $post(['password' => 'Yeni4567', 'password_confirmation' => 'Yeni4567'])->assertSessionHasNoErrors()->assertRedirect();
        $fresh = $student->fresh();
        $this->assertFalse($fresh->password_change_required);
        $this->assertNotNull($fresh->password_changed_at);
        $this->assertTrue(Hash::check('Yeni4567', $fresh->password));
        $this->assertSame('ali.veli@school.local', $fresh->email);
        $cred = StudentCredential::query()->where('student_id', $fresh->student()->first()->id)->firstOrFail();
        $this->assertSame('Yeni4567', $cred->plain_password);
        $this->assertSame('ali.veli', $cred->username);

        // Bir daha ekran gelmez
        $this->actingAs($fresh)->get(route('password.force.show'))->assertRedirect(route('dashboard'));
        $this->actingAs($fresh)->get(route('profile.edit'))->assertOk();
    }

    public function test_logout_stays_available_and_staff_is_never_forced(): void
    {
        $student = $this->mk('student', 'Zeynep', 'zeynep@school.local', true);
        $this->actingAs($student)->get(route('logout.get'))->assertRedirect();
        $this->assertGuest();

        $teacher = $this->mk('teacher', 'Öğretmen', 'ogr@school.local', true);   // bayrak olsa bile öğretmen etkilenmez
        $this->actingAs($teacher)->get(route('profile.edit'))->assertOk();
    }
}
