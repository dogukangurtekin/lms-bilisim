<?php

namespace Tests\Feature;

use App\Models\LiveQuiz;
use App\Models\LiveQuizAnswer;
use App\Models\LiveQuizParticipant;
use App\Models\LiveQuizQuestion;
use App\Models\LiveQuizSession;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use App\Support\PublicGamesSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLiveQuizTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $teacher;
    private LiveQuiz $quiz;

    protected function setUp(): void
    {
        parent::setUp();
        $ar = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $tr = Role::query()->firstOrCreate(['slug' => 'teacher'], ['name' => 'Teacher']);
        Role::query()->firstOrCreate(['slug' => 'student'], ['name' => 'Student']);
        $this->admin = User::query()->create(['role_id' => $ar->id, 'name' => 'Yönetici', 'email' => 'adm@t.l', 'password' => 'secret123', 'is_active' => true]);
        $this->teacher = User::query()->create(['role_id' => $tr->id, 'name' => 'Öğretmen', 'email' => 'tch@t.l', 'password' => 'secret123', 'is_active' => true]);
        $this->quiz = LiveQuiz::query()->create(['teacher_user_id' => $this->admin->id, 'title' => 'Genel Quiz', 'join_mode' => 'code', 'status' => 'active']);
        LiveQuizQuestion::query()->create(['live_quiz_id' => $this->quiz->id, 'sort_order' => 0, 'type' => 'multiple', 'question_text' => 'Soru 1', 'options' => ['A', 'B', 'C', 'D'], 'correct_answer' => 'A', 'duration_sec' => 20, 'xp' => 10, 'double_xp' => false]);
        @unlink(storage_path('app/public-games.json'));
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('app/public-games.json'));
        parent::tearDown();
    }

    private function enable(bool $on = true): void
    {
        PublicGamesSetting::save(false, [], 0, $on);
    }

    private function publicSession(bool $public = true, string $status = 'lobby'): LiveQuizSession
    {
        return LiveQuizSession::query()->create([
            'live_quiz_id' => $this->quiz->id, 'teacher_user_id' => $this->admin->id, 'join_code' => 'PUBLIC', 'status' => $status,
            'phase' => $status === 'live' ? 'question' : 'lobby', 'current_index' => 0, 'is_locked' => $status !== 'live',
            'started_at_ms' => $status === 'live' ? 1 : null,
            'ends_at_ms' => $status === 'live' ? (int) floor(microtime(true) * 1000) + 25000 : null,
            'is_public' => $public,
        ]);
    }

    private function joinData(array $o = []): array
    {
        return $o + ['join_code' => 'PUBLIC', 'first_name' => 'Ayşe', 'last_name' => 'Yılmaz'];
    }

    public function test_default_is_off_and_join_is_refused(): void
    {
        $this->assertFalse(PublicGamesSetting::liveQuizEnabled());
        $this->publicSession();
        $this->get(route('public.live-quiz.join'))->assertOk()->assertSee('şu an kapalı');
        $this->post(route('public.live-quiz.join.post'), $this->joinData())->assertSessionHasErrors('join_code');
        $this->assertSame(0, LiveQuizParticipant::query()->count());
    }

    public function test_guest_joins_with_code_and_name_and_plays(): void
    {
        $this->enable();
        $session = $this->publicSession(true, 'live');

        $this->post(route('public.live-quiz.join.post'), $this->joinData())->assertRedirect(route('student.live-quiz.play', $session));
        $guest = User::query()->where('name', 'Ayşe Yılmaz')->firstOrFail();
        $this->assertSame('guest', $guest->role->slug);
        $this->assertAuthenticatedAs($guest);

        $this->get(route('student.live-quiz.play', $session))->assertOk()->assertSee('Quizden ayrıl');
        $this->getJson(route('student.live-quiz.status', $session))->assertOk()->assertJsonPath('status', 'live');
        $this->post(route('student.live-quiz.answer', $session), ['question_index' => 0, 'answer' => 'A'])->assertRedirect();
        $this->assertSame(1, LiveQuizAnswer::query()->where('student_user_id', $guest->id)->count());
    }

    public function test_wrong_code_and_private_sessions_are_not_joinable(): void
    {
        $this->enable();
        $this->publicSession(false);
        $this->post(route('public.live-quiz.join.post'), $this->joinData())->assertSessionHasErrors('join_code');
        $this->post(route('public.live-quiz.join.post'), $this->joinData(['join_code' => 'ZZZZZZ']))->assertSessionHasErrors('join_code');
        $this->assertSame(0, User::query()->where('email', 'like', '%@guest.invalid')->count());
    }

    public function test_name_is_validated_and_duplicates_get_a_suffix(): void
    {
        $this->enable();
        $this->publicSession();
        $this->post(route('public.live-quiz.join.post'), $this->joinData(['first_name' => 'A1<script>']))->assertSessionHasErrors('first_name');
        $this->post(route('public.live-quiz.join.post'), $this->joinData(['last_name' => '']))->assertSessionHasErrors('last_name');

        $this->post(route('public.live-quiz.join.post'), $this->joinData())->assertRedirect();
        auth()->logout();
        $this->flushSession();
        $this->post(route('public.live-quiz.join.post'), $this->joinData())->assertRedirect();
        $this->assertSame(['Ayşe Yılmaz', 'Ayşe Yılmaz (2)'], User::query()->where('email', 'like', '%@guest.invalid')->orderBy('id')->pluck('name')->all());
    }

    public function test_guest_is_confined_to_the_quiz_screens(): void
    {
        $this->enable();
        $session = $this->publicSession(true, 'live');
        $this->post(route('public.live-quiz.join.post'), $this->joinData());

        $this->get(route('student.portal.dashboard'))->assertRedirect(route('public.live-quiz.join'));
        $this->get(route('student.messages.index'))->assertRedirect(route('public.live-quiz.join'));
        $this->getJson(route('student.live-quiz.active'))->assertForbidden();

        // başka (herkese açık olmayan) oturuma erişemez
        $other = LiveQuizSession::query()->create(['live_quiz_id' => $this->quiz->id, 'teacher_user_id' => $this->admin->id, 'join_code' => 'PRIV01', 'status' => 'live', 'phase' => 'question', 'current_index' => 0, 'is_locked' => false, 'started_at_ms' => 1, 'ends_at_ms' => (int) floor(microtime(true) * 1000) + 20000, 'is_public' => false]);
        $this->get(route('student.live-quiz.play', $other))->assertForbidden();
        $this->assertNotNull($session->id);
    }

    public function test_turning_the_setting_off_stops_guests_immediately(): void
    {
        $this->enable();
        $session = $this->publicSession(true, 'live');
        $this->post(route('public.live-quiz.join.post'), $this->joinData());
        $this->getJson(route('student.live-quiz.status', $session))->assertOk();

        $this->enable(false);
        $this->getJson(route('student.live-quiz.status', $session))->assertForbidden();
    }

    public function test_only_admin_can_start_a_public_session_and_only_when_enabled(): void
    {
        $this->actingAs($this->admin)->post(route('live-quiz.start', $this->quiz), ['is_public' => '1']);
        $this->assertFalse(LiveQuizSession::query()->latest('id')->first()->is_public);

        $this->enable();
        $this->actingAs($this->admin)->post(route('live-quiz.start', $this->quiz), ['is_public' => '1']);
        $this->assertTrue(LiveQuizSession::query()->latest('id')->first()->is_public);

        $tq = LiveQuiz::query()->create(['teacher_user_id' => $this->teacher->id, 'title' => 'Öğretmen Quiz', 'join_mode' => 'code', 'status' => 'active']);
        LiveQuizQuestion::query()->create(['live_quiz_id' => $tq->id, 'sort_order' => 0, 'type' => 'multiple', 'question_text' => 'S', 'options' => ['A', 'B'], 'correct_answer' => 'A', 'duration_sec' => 20, 'xp' => 10, 'double_xp' => false]);
        $this->actingAs($this->teacher)->post(route('live-quiz.start', $tq), ['is_public' => '1']);
        $this->assertFalse(LiveQuizSession::query()->latest('id')->first()->is_public);
    }

    public function test_finishing_logs_the_guest_out_and_writes_no_xp(): void
    {
        $this->enable();
        $session = $this->publicSession(true, 'live');
        $this->post(route('public.live-quiz.join.post'), $this->joinData());
        $guest = User::query()->where('name', 'Ayşe Yılmaz')->firstOrFail();
        $this->post(route('student.live-quiz.answer', $session), ['question_index' => 0, 'answer' => 'A']);

        $this->actingAs($this->admin)->post(route('live-quiz.session.finish', $session))->assertRedirect();
        $this->assertSame(0, UserProfile::query()->where('user_id', $guest->id)->count());

        $this->actingAs($guest)->get(route('student.live-quiz.play', $session))->assertRedirect(route('public.live-quiz.join'));
        $this->assertGuest();
    }

    public function test_setting_card_and_start_checkbox_show_for_admin(): void
    {
        $this->actingAs($this->admin)->get(route('profile.edit'))->assertOk()->assertSee('name="live_quiz"', false);
        $this->actingAs($this->admin)->post(route('profile.public-games.update'), ['enabled' => '1', 'live_quiz' => '1'])->assertRedirect();
        $this->assertTrue(PublicGamesSetting::liveQuizEnabled());
        $this->actingAs($this->admin)->get(route('live-quiz.index'))->assertSee('name="is_public"', false);
        $this->get(route('public.games'))->assertSee('Canlı Quiz');
        $this->actingAs($this->admin)->post(route('profile.public-games.update'), ['enabled' => '1'])->assertRedirect();
        $this->assertFalse(PublicGamesSetting::liveQuizEnabled());
    }
}
