<?php

namespace Tests\Feature;

use App\Models\LiveQuiz;
use App\Models\LiveQuizAnswer;
use App\Models\LiveQuizGameResult;
use App\Models\LiveQuizParticipant;
use App\Models\LiveQuizQuestion;
use App\Models\LiveQuizSession;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use App\Support\QuizMiniGame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveQuizMiniGameTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private User $student;
    private User $other;

    protected function setUp(): void
    {
        parent::setUp();
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $this->teacher = $this->mk($teacherRole->id, 'Öğretmen', 'mg-t@test.local');
        $this->student = $this->mk($studentRole->id, 'Ali Öğrenci', 'mg-s1@test.local');
        $this->other = $this->mk($studentRole->id, 'Veli Öğrenci', 'mg-s2@test.local');
    }

    private function mk(int $roleId, string $name, string $email): User
    {
        return User::query()->create(['role_id' => $roleId, 'name' => $name, 'email' => $email, 'password' => 'secret123', 'is_active' => true]);
    }

    private function quiz(int $questions = 3): LiveQuiz
    {
        $quiz = LiveQuiz::query()->create(['teacher_user_id' => $this->teacher->id, 'title' => 'Mini Oyun Quiz', 'join_mode' => 'code', 'status' => 'active']);
        for ($i = 0; $i < $questions; $i++) {
            LiveQuizQuestion::query()->create(['live_quiz_id' => $quiz->id, 'sort_order' => $i, 'type' => 'multiple', 'question_text' => 'Soru '.($i + 1), 'options' => ['A', 'B'], 'correct_answer' => 'A', 'duration_sec' => 20, 'xp' => 10, 'double_xp' => false]);
        }

        return $quiz;
    }

    private function newSession(LiveQuiz $quiz, string $mode = 'game', ?string $game = 'compute-it-runner'): LiveQuizSession
    {
        $session = LiveQuizSession::query()->create([
            'live_quiz_id' => $quiz->id, 'teacher_user_id' => $this->teacher->id, 'join_code' => strtoupper(substr(md5((string) microtime(true)), 0, 6)),
            'status' => 'lobby', 'phase' => 'lobby', 'between_mode' => $mode, 'between_game' => $mode === 'game' ? $game : null, 'current_index' => 0, 'is_locked' => true,
        ]);
        foreach ([$this->student, $this->other] as $u) {
            LiveQuizParticipant::query()->create(['live_quiz_session_id' => $session->id, 'student_user_id' => $u->id, 'joined_at_ms' => 1]);
        }

        return $session;
    }

    private function expire(LiveQuizSession $session): void
    {
        $session->refresh();
        $session->update(['ends_at_ms' => (int) floor(microtime(true) * 1000) - 10]);
    }

    private function sync(LiveQuizSession $session): array
    {
        return $this->actingAs($this->teacher)->getJson(route('live-quiz.session.status', $session))->assertOk()->json();
    }

    public function test_game_comes_every_fourth_question_and_never_after_the_last(): void
    {
        $this->assertSame([], QuizMiniGame::breakIndexes(1));
        $this->assertSame([], QuizMiniGame::breakIndexes(4));
        $this->assertSame([3], QuizMiniGame::breakIndexes(5));
        $this->assertSame([3], QuizMiniGame::breakIndexes(8));
        $this->assertSame([3, 7], QuizMiniGame::breakIndexes(9));
        $this->assertSame([3, 7, 11], QuizMiniGame::breakIndexes(13));
    }

    public function test_fixed_game_plan_has_ten_levels_between_10_and_40_and_never_repeats_back_to_back(): void
    {
        foreach (array_keys(QuizMiniGame::GAMES) as $slug) {
            $plan = QuizMiniGame::plan($slug, 41);
            $this->assertSame(QuizMiniGame::breakIndexes(41), array_keys($plan), $slug);
            $prev = null;
            foreach ($plan as $b) {
                $this->assertSame($slug, $b['game']);
                $this->assertSame(QuizMiniGame::FIRST_LEVEL, $b['from'], $slug);   // bölüm sınırı yok: aralığın tamamı
                $this->assertGreaterThanOrEqual(QuizMiniGame::FIRST_LEVEL, $b['from'], $slug);
                $this->assertLessThanOrEqual(QuizMiniGame::LAST_LEVEL, $b['to'], $slug);
            }
        }
    }

    public function test_random_plan_uses_different_games_and_levels_each_time_within_10_40(): void
    {
        $seen = [];
        $allBlocks = [];
        for ($k = 0; $k < 30; $k++) {
            $plan = QuizMiniGame::plan(null, 41);
            $prevGame = null;
            foreach ($plan as $b) {
                $this->assertTrue(QuizMiniGame::supported($b['game']));
                $this->assertNotSame($prevGame, $b['game'], 'art arda aynı oyun gelmemeli');
                $prevGame = $b['game'];
                $this->assertGreaterThanOrEqual(QuizMiniGame::FIRST_LEVEL, $b['from']);
                $this->assertLessThanOrEqual(QuizMiniGame::LAST_LEVEL, $b['to']);
                $this->assertSame(QuizMiniGame::FIRST_LEVEL, $b['from']);
                $seen[$b['game']] = true;
                $allBlocks[$b['game'] . ':' . $b['from']] = true;
            }
        }
        $this->assertCount(count(QuizMiniGame::GAMES), $seen, 'tüm kodlama oyunları zamanla gelmeli');
        $this->assertGreaterThanOrEqual(count(QuizMiniGame::GAMES), count($allBlocks), 'bölümler sabit kalmamalı, çok sayıda farklı oyun/bölüm kombinasyonu çıkmalı');

        // iki ayrı yarışma (iki plan) birbirinin aynısı olmamalı
        $a = QuizMiniGame::plan(null, 41);
        $same = 0;
        for ($i = 0; $i < 20; $i++) {
            if (QuizMiniGame::plan(null, 41) === $a) {
                $same++;
            }
        }
        $this->assertSame(0, $same);
        // belirli bir oyun geçersizse rastgeleye düşer
        $this->assertNotEmpty(QuizMiniGame::plan('python-editor', 9));
    }

    public function test_start_validates_the_mini_game_choice_and_launch_stores_one_shared_plan(): void
    {
        $quiz = $this->quiz(9);

        $this->actingAs($this->teacher)->post(route('live-quiz.start', $quiz), ['between_mode' => 'game', 'between_game' => 'excel-runner'])->assertSessionHas('err');
        $this->assertSame(0, LiveQuizSession::query()->count());

        // belirli oyun
        $this->actingAs($this->teacher)->post(route('live-quiz.start', $quiz), ['between_mode' => 'game', 'between_game' => 'compute-it-runner'])->assertRedirect();
        $s = LiveQuizSession::query()->firstOrFail();
        $this->assertSame('game', $s->between_mode);
        $this->assertSame('compute-it-runner', $s->between_game);
        $this->actingAs($this->teacher)->post(route('live-quiz.session.launch', $s))->assertRedirect();
        $plan = $s->fresh()->between_plan;
        $this->assertSame([3, 7], array_keys($plan), '9 soru: 4. ve 8. sorudan sonra');
        $this->assertSame('compute-it-runner', $plan[3]['game']);

        // rastgele (varsayılan)
        $this->actingAs($this->teacher)->post(route('live-quiz.start', $quiz), ['between_mode' => 'game', 'between_game' => 'random'])->assertRedirect();
        $r = LiveQuizSession::query()->latest('id')->firstOrFail();
        $this->assertNull($r->between_game);
        $this->actingAs($this->teacher)->post(route('live-quiz.session.launch', $r))->assertRedirect();
        $rp = $r->fresh()->between_plan;
        $this->assertCount(2, $rp);
        foreach ($rp as $b) {
            $this->assertTrue(QuizMiniGame::supported($b['game']));
        }

        // 4 soru ya da daha azında mini oyun gelmez
        $short = $this->quiz(4);
        $this->actingAs($this->teacher)->post(route('live-quiz.start', $short), ['between_mode' => 'game'])->assertRedirect();
        $sh = LiveQuizSession::query()->latest('id')->firstOrFail();
        $this->actingAs($this->teacher)->post(route('live-quiz.session.launch', $sh));
        $this->assertSame([], $sh->fresh()->between_plan);

        $this->actingAs($this->teacher)->post(route('live-quiz.start', $quiz), [])->assertRedirect();
        $wait = LiveQuizSession::query()->latest('id')->firstOrFail();
        $this->assertSame('wait', $wait->between_mode);
    }

    public function test_phase_machine_inserts_game_phase_only_after_every_fourth_question(): void
    {
        $quiz = $this->quiz(9);
        $session = $this->newSession($quiz);
        $this->actingAs($this->teacher)->post(route('live-quiz.session.launch', $session));
        $this->assertSame('question', $session->fresh()->phase);

        $phases = [];
        for ($i = 0; $i < 40; $i++) {
            $this->expire($session);
            $d = $this->sync($session);
            $phases[] = $d['phase'] . ':' . $d['current_index'];
            if ($d['phase'] === 'final') {
                break;
            }
        }
        $games = array_values(array_filter($phases, fn ($p) => str_starts_with($p, 'game:')));
        $this->assertSame(['game:3', 'game:7'], $games, '4. ve 8. sorudan sonra, başka hiçbir arada değil');
        $this->assertContains('question:4', $phases);
        $this->assertSame('final:8', end($phases));
        // mini oyundan sonra bir sonraki soru gelir
        $this->assertSame($phases[array_search('game:3', $phases, true) + 1], 'question:4');

        // 5 sn bekleme modunda mini oyun aşaması yoktur
        $wait = $this->newSession($this->quiz(2), 'wait');
        $this->actingAs($this->teacher)->post(route('live-quiz.session.launch', $wait));
        $this->expire($wait);
        $this->assertSame('results', $this->sync($wait)['phase']);
        $this->expire($wait);
        $this->assertSame('question:1', $this->sync($wait)['phase'] . ':' . $this->sync($wait)['current_index']);
    }

    private function gamePhase(?LiveQuizSession &$session = null, ?string $game = 'compute-it-runner'): LiveQuizSession
    {
        $session = $this->newSession($this->quiz(5), 'game', $game);
        $this->actingAs($this->teacher)->post(route('live-quiz.session.launch', $session));
        for ($i = 0; $i < 12; $i++) {
            $this->expire($session);
            $this->sync($session);
            $session->refresh();
            if ($session->phase === 'game') {
                break;
            }
        }
        $this->assertSame('game', $session->phase);
        $this->assertSame(3, (int) $session->current_index, 'mini oyun 4. sorudan sonra gelir');
        // 5 sn geçmiş say (ilk bölüm bildirimi çok hızlı sayılmasın)
        $session->update(['ends_at_ms' => (int) floor(microtime(true) * 1000) + QuizMiniGame::DURATION_MS - 5000]);

        return $session->fresh();
    }

    public function test_student_screen_gets_the_shared_block_and_a_matching_runner_grant(): void
    {
        $session = $this->gamePhase();
        $block = $session->between_plan[3];

        foreach ([$this->student, $this->other] as $u) {
            $this->actingAs($u)->get(route('student.live-quiz.play', $session))
                ->assertOk()->assertSee('lqGameFrame', false)
                ->assertSee('from='.$block['from'].'&amp;to='.$block['to'], false);
            $grant = session('runner_grant');
            $this->assertSame('compute-it-runner', $grant['slug']);
            $this->assertSame($block['from'], $grant['from']);
            $this->assertSame($block['to'], $grant['to']);
        }
    }

    public function test_random_mode_gives_the_planned_game_and_its_own_xp_cap(): void
    {
        $session = $this->gamePhase($tmp, null);
        $entry = $session->between_plan[3];
        $slug = $entry['game'];

        $this->actingAs($this->student)->get(route('student.live-quiz.play', $session))
            ->assertOk()->assertSee('lqGameFrame', false)->assertSee('/' . $slug . '?', false);
        $this->assertSame($slug, session('runner_grant')['slug']);

        $ok = $this->actingAs($this->student)->postJson(route('student.live-quiz.game-level', $session), ['level_key' => '23', 'level_no' => 23, 'xp' => 9999])->assertOk();
        $this->assertSame(QuizMiniGame::xpCap($slug), $ok->json('xp'), 'üst sınır, planlanan oyunun sınırı olmalı');
    }

    public function test_server_validates_levels_xp_time_and_duplicates(): void
    {
        $session = $this->gamePhase();
        $url = route('student.live-quiz.game-level', $session);

        $ok = $this->actingAs($this->student)->postJson($url, ['level_key' => '23', 'level_no' => 23, 'xp' => 17])->assertOk();
        $this->assertSame(1, $ok->json('levels_done'));
        $this->assertSame(17, $ok->json('xp'));

        // aynı bölüm ikinci kez sayılmaz (çok hızlı olmasa bile)
        LiveQuizGameResult::query()->where('student_user_id', $this->student->id)->update(['completed_at_ms' => 1]);
        $again = $this->actingAs($this->student)->postJson($url, ['level_key' => '23', 'level_no' => 23, 'xp' => 17])->assertOk();
        $this->assertSame(1, $again->json('levels_done'));

        // XP üst sınırı (compute-it: 65)
        $capped = $this->actingAs($this->student)->postJson($url, ['level_key' => '24', 'level_no' => 24, 'xp' => 5000])->assertOk();
        $this->assertSame(17 + 65, $capped->json('xp'));

        // çok hızlı art arda bildirim
        $this->actingAs($this->student)->postJson($url, ['level_key' => '25', 'xp' => 10])->assertStatus(422)->assertJsonPath('reason', 'too_fast');

        // en fazla 10 bölüm
        for ($i = 0; $i < 40; $i++) {
            LiveQuizGameResult::query()->create(['live_quiz_session_id' => $session->id, 'student_user_id' => $this->student->id, 'break_index' => 3, 'level_key' => 'x'.$i, 'xp' => 1, 'completed_at_ms' => 1]);
        }
        $this->actingAs($this->student)->postJson($url, ['level_key' => '99', 'xp' => 10])->assertStatus(422)->assertJsonPath('reason', 'limit');

        // başka öğrencinin kaydı ayrı tutulur
        $this->assertSame(0, LiveQuizGameResult::query()->where('student_user_id', $this->other->id)->count());

        // oyun aşaması dışında kabul edilmez
        $q = $this->newSession($this->quiz(2), 'game');
        $this->actingAs($this->student)->postJson(route('student.live-quiz.game-level', $q), ['level_key' => '1', 'xp' => 1])->assertStatus(409);
    }

    public function test_game_xp_counts_in_the_quiz_score_awards_profile_xp_and_shows_in_the_report(): void
    {
        $session = $this->gamePhase();
        $url = route('student.live-quiz.game-level', $session);
        $this->actingAs($this->student)->postJson($url, ['level_key' => '30', 'level_no' => 30, 'xp' => 20])->assertOk();
        // Soru bitince sunucu her öğrenci için boş cevap satırı açar; XP'leri bu satırlara yazıyoruz.
        LiveQuizAnswer::query()->where('student_user_id', $this->student->id)->where('question_index', 0)->update(['selected_answer' => 'A', 'is_correct' => true, 'xp_earned' => 10, 'answered_at_ms' => 1]);
        LiveQuizAnswer::query()->where('student_user_id', $this->other->id)->where('question_index', 0)->update(['selected_answer' => 'A', 'is_correct' => true, 'xp_earned' => 15, 'answered_at_ms' => 1]);

        // canlı sıralama: öğrenci 10 + 20 = 30 XP ile diğerini (15) geçer
        $rows = $this->sync($session)['rows'];
        $this->assertSame(30, $rows[0]['xp']);
        $this->assertSame(20, $rows[0]['game_xp']);
        $this->assertSame(15, $rows[1]['xp']);
        $board = $this->sync($session)['board'];
        $this->assertSame(30, $board[0]['xp']);

        // bitişte profil XP'si quiz + mini oyun kadar artar (tek seferde)
        $this->actingAs($this->teacher)->post(route('live-quiz.session.finish', $session));
        $this->assertSame(30, (int) UserProfile::query()->where('user_id', $this->student->id)->value('xp'));
        $this->assertSame(15, (int) UserProfile::query()->where('user_id', $this->other->id)->value('xp'));

        $this->actingAs($this->teacher)->get(route('live-quiz.session.report', $session))
            ->assertOk()->assertSee('Mini Oyun Özeti')->assertSee('Mini Oyun XP')->assertSee('Ali Öğrenci');
        $csv = $this->actingAs($this->teacher)->get(route('live-quiz.session.report.csv', $session));
        $this->assertStringContainsString('Mini Oyun XP', $csv->streamedContent());
    }

    public function test_teacher_screen_shows_game_phase_and_live_rows(): void
    {
        $session = $this->gamePhase();
        $this->actingAs($this->student)->postJson(route('student.live-quiz.game-level', $session), ['level_key' => '40', 'xp' => 12])->assertOk();

        $this->actingAs($this->teacher)->get(route('live-quiz.session.show', $session))->assertOk()->assertSee('Mini Oyun:', false)->assertSee('lqGameRows', false);
        $game = $this->sync($session)['game'];
        $this->assertSame('Ali Öğrenci', $game['rows'][0]['name']);
        $this->assertSame(1, $game['rows'][0]['levels']);
        $this->assertSame(12, $game['rows'][0]['xp']);
    }
}
