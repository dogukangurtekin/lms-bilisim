<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentNameLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_change_name_but_can_change_password(): void
    {
        $role = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $u = User::query()->create(['role_id' => $role->id, 'name' => 'Ali Veli', 'email' => 'ali.veli@school.local', 'password' => 'secret123', 'is_active' => true]);
        $u->forceFill(['password_change_required' => false])->save();

        $this->actingAs($u->fresh())->put(route('profile.update'), [
            'first_name' => 'Hacker', 'last_name' => '101', 'username' => 'x',
            'password' => 'yenisifre1', 'password_confirmation' => 'yenisifre1',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Ali Veli', $u->fresh()->name);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('yenisifre1', $u->fresh()->password));

        // ad/soyad hiç gönderilmese de şifre değişir
        $this->actingAs($u->fresh())->put(route('profile.update'), ['password' => 'baska1234', 'password_confirmation' => 'baska1234'])->assertSessionHasNoErrors();
        $this->assertSame('Ali Veli', $u->fresh()->name);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('baska1234', $u->fresh()->password));
    }

    public function test_teacher_can_still_change_own_name(): void
    {
        $role = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $t = User::query()->create(['role_id' => $role->id, 'name' => 'Hoca Bir', 'email' => 'hoca@school.local', 'password' => 'secret123', 'is_active' => true]);
        $this->actingAs($t)->put(route('profile.update'), ['first_name' => 'Hoca', 'last_name' => 'Iki', 'username' => 'hoca'])->assertSessionHasNoErrors();
        $this->assertSame('Hoca Iki', $t->fresh()->name);
    }
}
