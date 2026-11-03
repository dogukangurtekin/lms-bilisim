<?php

namespace Tests\Feature;

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CompetitionController;
use App\Models\Role;
use App\Models\User;
use App\Support\PublicGamesSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlexGoletiTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_is_registered_everywhere_it_must_be(): void
    {
        $games = ActivityController::games();
        $this->assertArrayHasKey('flex-goleti-runner', $games);
        $this->assertFileExists(public_path($games['flex-goleti-runner']['image']));
        $this->assertContains('flex-goleti-runner', CompetitionController::ELIGIBLE_SLUGS);
        $this->assertArrayHasKey('flex-goleti-runner', PublicGamesSetting::GAMES);
        foreach (['index.html', 'app.js', 'levels.js', 'style.css'] as $f) {
            $this->assertFileExists(public_path('runner-assets/flex-goleti-runner/' . $f));
        }
    }

    public function test_staff_can_open_the_game_page_with_runner_injection(): void
    {
        $role = Role::query()->firstOrCreate(['slug' => 'teacher'], ['name' => 'Teacher']);
        $teacher = User::query()->create(['role_id' => $role->id, 'name' => 'Hoca', 'email' => 'h@t.l', 'password' => 'secret123', 'is_active' => true]);

        $this->actingAs($teacher)->get('/flex-goleti-runner?role=teacher')
            ->assertOk()
            ->assertSee('Flex Göleti')
            ->assertSee('window.RUNNER_USER', false)
            ->assertSee('runner-assets/flex-goleti-runner/app.js?v=', false)
            ->assertSee('runner-assets/flex-goleti-runner/levels.js?v=', false);
    }

    public function test_public_page_works_only_when_the_game_is_opened_by_admin(): void
    {
        @unlink(storage_path('app/public-games.json'));
        // Yönetici açana kadar (ana anahtar açık olsa bile) herkese açık listede kapalıdır.
        $this->get(route('public.game', 'flex-goleti-runner'))->assertNotFound();
        PublicGamesSetting::save(true, [], 0);
        $this->get(route('public.game', 'flex-goleti-runner'))->assertNotFound();

        PublicGamesSetting::save(true, ['flex-goleti-runner' => true], 3);
        $this->get(route('public.game', 'flex-goleti-runner'))->assertOk()->assertSee('RUNNER_PUBLIC_LIMIT=3', false);
        @unlink(storage_path('app/public-games.json'));
    }

    public function test_level_set_has_100_levels_in_five_categories(): void
    {
        $js = (string) file_get_contents(public_path('runner-assets/flex-goleti-runner/levels.js'));
        foreach (["'kolay'", "'orta'", "'orta-zor'", "'zor'", "'uzman'"] as $cat) {
            $this->assertStringContainsString($cat, $js);
        }
        // Her seviye satırı ['kategori', n, {...}] biçiminde; toplam 100.
        $this->assertSame(100, preg_match_all("/^\s*\['(kolay|orta|orta-zor|zor|uzman)', \d+, \{/m", $js));
    }
}
