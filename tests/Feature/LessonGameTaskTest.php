<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Support\SlideTimerSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonGameTaskTest extends TestCase
{
    use RefreshDatabase;

    private string $settingFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingFile = storage_path('app/slide-timer.json');
        @file_put_contents($this->settingFile, json_encode(['default' => 'game', 'groups' => []]));
        $this->resetSettingCache();
    }

    protected function tearDown(): void
    {
        @unlink($this->settingFile);
        $this->resetSettingCache();
        parent::tearDown();
    }

    private function resetSettingCache(): void
    {
        $p = new \ReflectionProperty(SlideTimerSetting::class, 'data');
        $p->setAccessible(true);
        $p->setValue(null, null);
    }

    public function test_lesson_game_task_picks_random_medium_levels_without_immediate_repeat(): void
    {
        $role = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $user = User::query()->create(['role_id' => $role->id, 'name' => 'Öğrenci', 'email' => 'lg@school.local', 'password' => 'secret123', 'is_active' => true]);

        $allowed = array_merge(range(21, 30), range(35, 60));
        $seen = [];
        $previous = null;
        for ($i = 1; $i <= 40; $i++) {
            $res = $this->actingAs($user)->getJson(route('student.portal.lesson-game-task', $i))->assertOk();
            $level = (int) $res->json('level');
            $this->assertContains($level, $allowed);
            $this->assertNotSame($previous, $level, 'Art arda aynı level gelmemeli');
            $this->assertStringContainsString("from={$level}&to={$level}", (string) $res->json('url'));
            $this->assertSame($level, session('runner_grant.from'));
            $previous = $level;
            $seen[$level] = true;
        }
        $this->assertGreaterThan(8, count($seen), 'Seviyeler rastgele dağılmalı');
    }
}
