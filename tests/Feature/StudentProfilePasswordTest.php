<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\StudentCredential;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentProfilePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_with_turkish_username_or_single_name_can_change_password_with_any_password(): void
    {
        $role = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        foreach ([['Ada Görgülü', 'agörgülü.5a@school.local'], ['Tekisim', 'tekisim.5a@school.local'], ['Ali İbrahim Karabağ', 'akarabağ.5a@school.local']] as [$name, $email]) {
            $u = User::query()->create(['role_id' => $role->id, 'name' => $name, 'email' => $email, 'password' => 'secret123', 'is_active' => true]);
            $u->forceFill(['password_change_required' => false])->save();
            StudentCredential::query()->create(['student_id' => $u->student()->firstOrFail()->id, 'username' => explode('@', $email)[0], 'plain_password' => 'secret123']);

            $parts = preg_split('/\s+/', $name, 2);
            // form, ad/soyad alanlarını salt okunur gönderir (boş soyad dahil); kolay şifre de kabul edilir
            $this->actingAs($u->fresh())->put(route('profile.update'), [
                'first_name' => $parts[0], 'last_name' => $parts[1] ?? '', 'username' => explode('@', $email)[0],
                'password' => '123456', 'password_confirmation' => '123456',
            ])->assertSessionHasNoErrors();
            $this->assertTrue(Hash::check('123456', $u->fresh()->password), $name);
            $this->assertSame($name, $u->fresh()->name);
            $this->assertSame($email, $u->fresh()->email);
            $this->assertSame('123456', StudentCredential::query()->where('student_id', $u->student()->first()->id)->value('plain_password'));

            // yalnızca şifre alanları gönderilse de çalışır
            $this->actingAs($u->fresh())->put(route('profile.update'), ['password' => 'abc123', 'password_confirmation' => 'abc123'])->assertSessionHasNoErrors();
            $this->assertTrue(Hash::check('abc123', $u->fresh()->password), $name);
        }
    }
}
