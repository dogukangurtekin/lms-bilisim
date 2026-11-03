<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConsentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['consent.enforced' => true, 'consent.version' => 1]);
    }

    private function user(string $slug, string $mail): User
    {
        $role = Role::query()->firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);

        return User::query()->create(['role_id' => $role->id, 'name' => ucfirst($slug) . ' Kişi', 'email' => $mail, 'password' => 'secret123', 'is_active' => true]);
    }

    public function test_everyone_must_accept_once_then_is_not_asked_again(): void
    {
        foreach (['student' => route('student.portal.dashboard'), 'teacher' => route('dashboard'), 'admin' => route('dashboard')] as $slug => $url) {
            $u = $this->user($slug, "$slug@t.l");

            $this->actingAs($u)->get($url)->assertRedirect(route('consent.show'));
            $this->actingAs($u)->get(route('consent.show'))->assertOk()->assertSee('Okudum, Onayladım')->assertSee('Tam metni oku');

            $this->actingAs($u)->post(route('consent.accept'))->assertRedirect();
            $u->refresh();
            $this->assertSame(1, (int) $u->consent_version);
            $this->assertNotNull($u->consent_accepted_at);
            $this->actingAs($u)->get($url)->assertDontSee('Okudum, Onayladım');
            $this->assertNotSame(route('consent.show'), $this->actingAs($u)->get($url)->headers->get('Location'));
        }
        $this->assertSame(3, DB::table('user_consents')->count());
    }

    public function test_student_sees_child_friendly_summary_and_staff_sees_formal_one(): void
    {
        $this->actingAs($this->user('student', 's@t.l'))->get(route('consent.show'))->assertSee('Okulun senin için bir hesap açtı');
        $this->actingAs($this->user('teacher', 't@t.l'))->get(route('consent.show'))->assertSee('veri sorumlusudur');
    }

    public function test_new_version_asks_everyone_again_and_ajax_gets_423(): void
    {
        $u = $this->user('teacher', 't2@t.l');
        $this->actingAs($u)->post(route('consent.accept'));
        $u->refresh();
        $this->assertNotSame(route('consent.show'), $this->actingAs($u)->get(route('dashboard'))->headers->get('Location'));

        config(['consent.version' => 2]);
        $this->actingAs($u)->get(route('dashboard'))->assertRedirect(route('consent.show'));
        $this->actingAs($u)->getJson(route('dashboard'))->assertStatus(423);
        $this->actingAs($u)->post(route('consent.accept'));
        $this->assertSame(2, (int) $u->fresh()->consent_version);
        $this->assertSame(2, DB::table('user_consents')->where('user_id', $u->id)->count());
    }

    public function test_legal_pages_and_logout_stay_reachable_and_guests_are_exempt(): void
    {
        $u = $this->user('student', 's3@t.l');
        $this->actingAs($u)->get(route('legal.privacy'))->assertOk();
        $this->actingAs($u)->get(route('legal.terms'))->assertOk();
        $this->actingAs($u)->get(route('logout.get'))->assertRedirect();

        $g = $this->user('guest', 'guest-x@guest.invalid');
        $this->actingAs($g)->get(route('public.live-quiz.join'))->assertOk();
    }

    public function test_user_returns_to_the_page_they_wanted(): void
    {
        $u = $this->user('admin', 'a9@t.l');
        $this->actingAs($u)->get(route('profile.edit'))->assertRedirect(route('consent.show'));
        $this->actingAs($u)->post(route('consent.accept'))->assertRedirect(route('profile.edit'));
    }
}
