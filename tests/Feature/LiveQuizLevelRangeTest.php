<?php

namespace Tests\Feature;

use App\Models\LiveQuiz;
use App\Models\LiveQuizQuestion;
use App\Models\LiveQuizSession;
use App\Models\Role;
use App\Models\User;
use App\Support\QuizMiniGame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveQuizLevelRangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_blocks_stay_inside_the_chosen_range(): void
    {
        for ($i = 0; $i < 20; $i++) {
            foreach (QuizMiniGame::plan(null, 25, null, 40, 60) as $b) {
                $this->assertGreaterThanOrEqual(40, $b['from']);
                $this->assertLessThanOrEqual(60, $b['to']);
                $this->assertSame(40, $b['from']);   // aralığın tamamı açık, 10'luk blok yok
            }
        }
        // varsayılan aralık 5-30
        foreach (QuizMiniGame::plan(null, 25) as $b) {
            $this->assertGreaterThanOrEqual(5, $b['from']);
            $this->assertLessThanOrEqual(30, $b['to']);
        }
        // bölüm sayısı yetmeyen oyun havuzdan düşer: 150-170 aralığında yalnızca 200 bölümlü oyunlar
        foreach (QuizMiniGame::plan(null, 25, null, 150, 170) as $b) {
            $this->assertContains($b['game'], ['block-grid-runner', 'block-3d-runner']);
        }
        $this->assertSame([], QuizMiniGame::plan(null, 25, null, 190, 195));   // 10'dan az bölüm
    }

    public function test_start_saves_the_range_and_rejects_bad_ranges(): void
    {
        $role = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $teacher = User::query()->create(['role_id' => $role->id, 'name' => 'Hoca', 'email' => 'h@t.l', 'password' => 'secret123', 'is_active' => true]);
        $quiz = LiveQuiz::query()->create(['teacher_user_id' => $teacher->id, 'title' => 'Aralık', 'join_mode' => 'code', 'status' => 'active']);
        LiveQuizQuestion::query()->create(['live_quiz_id' => $quiz->id, 'sort_order' => 0, 'type' => 'multiple', 'question_text' => 'S', 'options' => ['A', 'B'], 'correct_answer' => 'A', 'duration_sec' => 30, 'xp' => 10, 'double_xp' => false]);

        $this->actingAs($teacher)->post(route('live-quiz.start', $quiz), ['between' => 'random', 'level_from' => 40, 'level_to' => 60])->assertSessionHas('ok');
        $s = LiveQuizSession::query()->latest('id')->firstOrFail();
        $this->assertSame([40, 60], [(int) $s->between_from, (int) $s->between_to]);

        // 10'dan az bölüm → reddedilir, yeni oturum açılmaz
        $count = LiveQuizSession::query()->count();
        $this->actingAs($teacher)->post(route('live-quiz.start', $quiz), ['between' => 'random', 'level_from' => 10, 'level_to' => 15])->assertSessionHas('err');
        $this->assertSame($count, LiveQuizSession::query()->count());

        // tek oyunda bölüm sayısını aşan aralık → reddedilir (kelime arısı 100 bölüm)
        $this->actingAs($teacher)->post(route('live-quiz.start', $quiz), ['between' => 'word-bee-runner', 'level_from' => 150, 'level_to' => 170])->assertSessionHas('err');
        $this->assertSame($count, LiveQuizSession::query()->count());

        // bekleme modunda aralık kaydedilmez
        $this->actingAs($teacher)->post(route('live-quiz.start', $quiz), ['between' => 'wait', 'level_from' => 40, 'level_to' => 60])->assertSessionHas('ok');
        $this->assertNull(LiveQuizSession::query()->latest('id')->first()->between_from);
    }

    public function test_game_duration_is_chosen_per_session_and_clamped(): void
    {
        $this->assertSame(30000, QuizMiniGame::durationMs(null));
        $this->assertSame(45000, QuizMiniGame::durationMs(45));
        $this->assertSame(10000, QuizMiniGame::durationMs(3));
        $this->assertSame(300000, QuizMiniGame::durationMs(9999));

        $role = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $teacher = User::query()->create(['role_id' => $role->id, 'name' => 'Hoca', 'email' => 'h@t.l', 'password' => 'secret123', 'is_active' => true]);
        $quiz = LiveQuiz::query()->create(['teacher_user_id' => $teacher->id, 'title' => 'Süre', 'join_mode' => 'code', 'status' => 'active']);
        LiveQuizQuestion::query()->create(['live_quiz_id' => $quiz->id, 'sort_order' => 0, 'type' => 'multiple', 'question_text' => 'S', 'options' => ['A', 'B'], 'correct_answer' => 'A', 'duration_sec' => 30, 'xp' => 10, 'double_xp' => false]);

        $this->actingAs($teacher)->post(route('live-quiz.start', $quiz), ['between' => 'random', 'game_seconds' => 60])->assertSessionHas('ok');
        $this->assertSame(60, (int) LiveQuizSession::query()->latest('id')->first()->between_seconds);

        $this->actingAs($teacher)->post(route('live-quiz.start', $quiz), ['between' => 'random', 'game_seconds' => 2])->assertSessionHas('ok');
        $this->assertSame(10, (int) LiveQuizSession::query()->latest('id')->first()->between_seconds);

        $this->actingAs($teacher)->post(route('live-quiz.start', $quiz), ['between' => 'random'])->assertSessionHas('ok');
        $this->assertSame(30, (int) LiveQuizSession::query()->latest('id')->first()->between_seconds);

        $this->actingAs($teacher)->post(route('live-quiz.start', $quiz), ['between' => 'wait', 'game_seconds' => 60])->assertSessionHas('ok');
        $this->assertNull(LiveQuizSession::query()->latest('id')->first()->between_seconds);
    }
}
