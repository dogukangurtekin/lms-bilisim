<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForcePasswordCsrfTest extends TestCase
{
    use RefreshDatabase;

    private function flaggedStudent(): User
    {
        $role = Role::query()->firstOrCreate(['slug' => 'student'], ['name' => 'Student']);
        $u = User::query()->create(['role_id' => $role->id, 'name' => 'Ali Veli', 'email' => 'csrf.ali@school.local', 'password' => 'Eski123', 'is_active' => true]);
        User::query()->whereKey($u->id)->update(['password_change_required' => true]);

        return $u->fresh();
    }

    public function test_screen_is_not_cacheable_and_a_fresh_token_endpoint_works_while_locked(): void
    {
        $student = $this->flaggedStudent();

        $show = $this->actingAs($student)->get(route('password.force.show'))->assertOk();
        $this->assertStringContainsString('no-store', (string) $show->headers->get('Cache-Control'));
        $show->assertSee('data-token-url', false)->assertSee('id="pwForm"', false);

        $token = $this->actingAs($student)->getJson(route('password.force.token'))->assertOk();
        $this->assertNotEmpty($token->json('token'));
        $this->assertStringContainsString('no-store', (string) $token->headers->get('Cache-Control'));
    }

    public function test_csrf_mismatch_on_the_password_screen_returns_to_the_form_instead_of_a_419_page(): void
    {
        $student = $this->flaggedStudent();

        // CSRF denetimini her zaman uyuşmazlık verecek şekilde değiştir (testlerde normalde atlanır)
        $mismatch = fn ($app) => new class($app, $app['encrypter']) extends VerifyCsrfToken {
            public function handle($request, \Closure $next)
            {
                throw new TokenMismatchException('CSRF token mismatch.');
            }
        };
        foreach ([VerifyCsrfToken::class, \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class] as $class) {
            $this->app->bind($class, $mismatch);
        }
        Log::spy();

        $this->actingAs($student)->post(route('password.force.update'), ['password' => 'Yeni4567x', 'password_confirmation' => 'Yeni4567x'])
            ->assertRedirect(route('password.force.show'))
            ->assertSessionHasErrors('password');

        Log::shouldHaveReceived('warning')->withArgs(fn ($m) => $m === 'password_force_csrf_mismatch')->once();
        $this->assertTrue((bool) $student->fresh()->password_change_required, 'Uyuşmayan belirteçle şifre değişmemeli');
    }
}
