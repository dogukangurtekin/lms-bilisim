<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameLoginRemovedTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_no_game_login_but_keeps_password_and_qr_login(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();
        foreach (['Oyun ile Giriş', 'openGameLoginBtn', 'miniGameModal', 'loginGameCanvas', 'login/game', 'Kanatli Kahraman'] as $gone) {
            $this->assertStringNotContainsString($gone, $html, $gone);
        }
        $this->assertStringContainsString('QR ile Giriş', $html);
        $this->assertStringContainsString('name="password"', $html);
    }

    public function test_username_only_game_login_endpoint_no_longer_exists(): void
    {
        $role = Role::query()->firstOrCreate(['slug' => 'student'], ['name' => 'Student']);
        User::query()->create(['role_id' => $role->id, 'name' => 'Ali', 'email' => 'ali@okul.local', 'password' => 'secret123', 'is_active' => true]);

        $this->postJson('/login/game', ['email' => 'ali', 'game_won' => true])->assertStatus(404);
        $this->assertGuest();
    }
}
