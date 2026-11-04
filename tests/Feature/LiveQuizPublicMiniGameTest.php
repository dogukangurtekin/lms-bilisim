<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Support\PublicGamesSetting;
use App\Support\QuizMiniGame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveQuizPublicMiniGameTest extends TestCase
{
    use RefreshDatabase;

    private function resetSetting(): void
    {
        @unlink(storage_path('app/public-games.json'));
        $p = new \ReflectionProperty(PublicGamesSetting::class, 'data');
        $p->setAccessible(true);
        $p->setValue(null, null);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetSetting();
    }

    protected function tearDown(): void
    {
        $this->resetSetting();
        parent::tearDown();
    }

    private function guest(): User
    {
        $role = Role::query()->firstOrCreate(['slug' => 'guest'], ['name' => 'Guest']);

        return User::query()->create(['role_id' => $role->id, 'name' => 'Misafir', 'email' => 'g@t.l', 'password' => 'secret123', 'is_active' => true]);
    }

    private function grant(string $slug): array
    {
        return ['runner_grant' => ['slug' => $slug, 'from' => 5, 'to' => 14, 'homework_id' => 'quizgame-1-3', 'expires_at' => now()->addHour()->timestamp]];
    }

    public function test_public_pool_follows_the_profile_switches(): void
    {
        PublicGamesSetting::save(true, ['word-bee-runner' => true, 'number-bee-runner' => true], 0, true);
        $this->assertEqualsCanonicalizing(['word-bee-runner', 'number-bee-runner'], QuizMiniGame::publicGames());

        $plan = QuizMiniGame::plan(null, 21, QuizMiniGame::publicGames());
        $this->assertNotEmpty($plan);
        foreach ($plan as $block) {
            $this->assertContains($block['game'], ['word-bee-runner', 'number-bee-runner']);
        }

        // hiç oyun açık değil → plan boş
        PublicGamesSetting::save(true, [], 0, true);
        $this->assertSame([], QuizMiniGame::publicGames());
        $this->assertSame([], QuizMiniGame::plan(null, 21, QuizMiniGame::publicGames()));

        // genel anahtar kapalıysa da kapalı
        PublicGamesSetting::save(false, ['word-bee-runner' => true], 0, true);
        $this->assertSame([], QuizMiniGame::publicGames());

        // normal (herkese açık olmayan) oturum etkilenmez
        $this->assertNotEmpty(QuizMiniGame::plan(null, 13));
    }

    public function test_guest_can_open_mini_game_only_when_enabled_and_granted(): void
    {
        $guest = $this->guest();
        $url = '/word-bee-runner?from=5&to=14&assignmentId=quizgame-1-3';

        // profilde açık + izin var → oyun açılır, izin uç noktası aralığı verir
        PublicGamesSetting::save(true, ['word-bee-runner' => true], 0, true);
        $this->actingAs($guest)->withSession($this->grant('word-bee-runner'))->get($url)->assertOk();
        $this->actingAs($guest)->withSession($this->grant('word-bee-runner'))->getJson('/runner-grant/word-bee-runner?assignmentId=quizgame-1-3')
            ->assertOk()->assertJson(['ok' => true, 'role' => 'student', 'from' => 5, 'to' => 14]);

        // izin yok → kapalı
        $this->flushSession();
        $this->actingAs($guest)->get($url)->assertForbidden();
        // başka oyunun izni → kapalı
        $this->flushSession();
        $this->actingAs($guest)->withSession($this->grant('number-bee-runner'))->get($url)->assertForbidden();

        // profilde kapalı → izin olsa bile kapalı
        $this->flushSession();
        PublicGamesSetting::save(true, ['word-bee-runner' => false], 0, true);
        $this->actingAs($guest)->withSession($this->grant('word-bee-runner'))->get($url)->assertForbidden();
        $this->actingAs($guest)->withSession($this->grant('word-bee-runner'))->getJson('/runner-grant/word-bee-runner?assignmentId=quizgame-1-3')->assertForbidden();

        // misafir sistemin başka sayfalarına yine giremez
        PublicGamesSetting::save(true, ['word-bee-runner' => true], 0, true);
        $this->actingAs($guest)->withSession($this->grant('word-bee-runner'))->get('/etkinlikler')->assertRedirect(route('public.live-quiz.join'));
    }
}
