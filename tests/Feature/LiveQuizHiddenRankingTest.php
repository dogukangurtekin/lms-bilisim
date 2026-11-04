<?php

namespace Tests\Feature;

use App\Models\LiveQuiz;
use App\Models\LiveQuizParticipant;
use App\Models\LiveQuizQuestion;
use App\Models\LiveQuizSession;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveQuizHiddenRankingTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    /** @var User[] */
    private array $students = [];
    private LiveQuizSession $session;

    protected function setUp(): void
    {
        parent::setUp();
        $tr = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $sr = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $this->teacher = User::query()->create(['role_id' => $tr->id, 'name' => 'Hoca', 'email' => 'h@t.l', 'password' => 'secret123', 'is_active' => true]);
        $quiz = LiveQuiz::query()->create(['teacher_user_id' => $this->teacher->id, 'title' => 'Gizli Sıralama', 'join_mode' => 'code', 'status' => 'active']);
        for ($i = 0; $i < 2; $i++) {
            LiveQuizQuestion::query()->create(['live_quiz_id' => $quiz->id, 'sort_order' => $i, 'type' => 'multiple', 'question_text' => 'Soru ' . ($i + 1), 'options' => ['Doğru', 'Yanlış'], 'correct_answer' => 'A', 'duration_sec' => 30, 'xp' => 10, 'double_xp' => false]);
        }
        $now = (int) floor(microtime(true) * 1000);
        $this->session = LiveQuizSession::query()->create([
            'live_quiz_id' => $quiz->id, 'teacher_user_id' => $this->teacher->id, 'join_code' => 'HIDE01', 'status' => 'live', 'phase' => 'question',
            'current_index' => 0, 'is_locked' => false, 'started_at_ms' => $now, 'ends_at_ms' => $now + 30000,
        ]);
        foreach (['Ali', 'Veli', 'Can'] as $i => $name) {
            $u = User::query()->create(['role_id' => $sr->id, 'name' => $name, 'email' => "s$i@t.l", 'password' => 'secret123', 'is_active' => true]);
            $this->students[$name] = $u;
            LiveQuizParticipant::query()->create(['live_quiz_session_id' => $this->session->id, 'student_user_id' => $u->id, 'joined_at_ms' => 1]);
        }
    }

    private function answer(string $name, string $choice): void
    {
        $this->actingAs($this->students[$name])->post(route('student.live-quiz.answer', $this->session), ['question_index' => 0, 'answer' => $choice])->assertRedirect();
    }

    private function teacherStatus(): array
    {
        return $this->actingAs($this->teacher)->getJson(route('live-quiz.session.status', $this->session))->assertOk()->json();
    }

    public function test_ranking_and_correct_wrong_counts_stay_frozen_until_everyone_answered(): void
    {
        $this->answer('Ali', 'A');                                  // doğru

        $d = $this->teacherStatus();
        $this->assertSame('question', $d['phase']);
        $this->assertSame(1, $d['stats']['answered']);
        $this->assertNull($d['stats']['correct']);
        $this->assertNull($d['stats']['wrong']);
        $this->assertSame([], $d['rows'], 'sıralama soru açıkken güncellenmez');
        foreach ($d['board'] as $row) {
            $this->assertSame(0, $row['xp'], 'canlı pano soru açıkken puan göstermez');
        }

        $this->answer('Veli', 'B');                                 // yanlış
        $d = $this->teacherStatus();
        $this->assertSame('question', $d['phase']);
        $this->assertSame([], $d['rows']);
        $this->assertNull($d['stats']['correct']);

        $this->answer('Can', 'A');                                  // herkes cevapladı → sonuç aşaması
        $d = $this->teacherStatus();
        $this->assertSame('results', $d['phase']);
        $this->assertSame([2, 1], [$d['stats']['correct'], $d['stats']['wrong']]);
        $this->assertCount(3, $d['rows']);
        $xp = collect($d['board'])->pluck('xp', 'student_name');
        $this->assertGreaterThan(0, $xp['Ali']);
        $this->assertGreaterThan(0, $xp['Can']);
        $this->assertSame(0, $xp['Veli']);
    }

    public function test_student_never_receives_rank_correctness_or_top_five_before_the_results(): void
    {
        $r = $this->actingAs($this->students['Ali'])->post(route('student.live-quiz.answer', $this->session), ['question_index' => 0, 'answer' => 'A']);
        $r->assertRedirect();
        $this->assertSame(['question_index' => 0], session('answer_feedback'), 'cevap sonrası öğrenciye doğru/yanlış, XP ya da sıralama taşınmaz');

        $page = $this->actingAs($this->students['Ali'])->get(route('student.live-quiz.play', $this->session))->assertOk();
        $page->assertSee('Cevabın Kaydedildi')->assertDontSee('Doğru Cevap')->assertDontSee('Yanlış Cevap')->assertDontSee('lq-board', false);

        $this->actingAs($this->students['Ali'])->getJson(route('student.live-quiz.status', $this->session))->assertOk()->assertJsonMissingPath('top_five');
    }

    public function test_results_and_final_screens_show_only_own_result_never_a_ranking(): void
    {
        $this->answer('Ali', 'A');
        $this->answer('Veli', 'B');
        $this->answer('Can', 'A');                                  // sonuç aşaması

        $res = $this->actingAs($this->students['Ali'])->get(route('student.live-quiz.play', $this->session))->assertOk();
        $res->assertSee('Doğru Cevap!')->assertSee('XP kazandın')->assertDontSee('lq-board', false)->assertDontSee('Sıralaman');
        $this->actingAs($this->students['Veli'])->get(route('student.live-quiz.play', $this->session))->assertSee('Yanlış Cevap')->assertDontSee('lq-board', false);
        $this->actingAs($this->students['Ali'])->getJson(route('student.live-quiz.status', $this->session))->assertOk()->assertJsonMissingPath('top_five');

        $this->session->update(['phase' => 'final', 'is_locked' => true]);
        $final = $this->actingAs($this->students['Ali'])->get(route('student.live-quiz.play', $this->session))->assertOk();
        $final->assertSee('Yarışma Tamamlandı')->assertSee('Toplam:')->assertSee('doğru cevap')->assertDontSee('Sıralaman')->assertDontSee('lq-board', false);

        // öğretmen ekranı sıralamayı gösterir
        $this->actingAs($this->teacher)->get(route('live-quiz.session.show', $this->session))->assertOk()->assertSee('lq-board', false);
    }
}
