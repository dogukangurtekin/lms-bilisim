<?php

namespace Tests\Feature;

use App\Models\LiveQuiz;
use App\Models\LiveQuizAnswer;
use App\Models\LiveQuizParticipant;
use App\Models\LiveQuizQuestion;
use App\Models\LiveQuizSession;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveQuizReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_report_has_summary_charts_hardest_question_and_csv(): void
    {
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $teacher = User::query()->create(['role_id' => $teacherRole->id, 'name' => 'Öğretmen', 'email' => 'qr-t@test.local', 'password' => 'secret123', 'is_active' => true]);
        $other = User::query()->create(['role_id' => $teacherRole->id, 'name' => 'Diğer', 'email' => 'qr-o@test.local', 'password' => 'secret123', 'is_active' => true]);

        $quiz = LiveQuiz::query()->create(['teacher_user_id' => $teacher->id, 'title' => 'Algoritma Quiz', 'join_mode' => 'code', 'status' => 'active']);
        foreach (['Birinci soru', 'İkinci soru'] as $i => $text) {
            LiveQuizQuestion::query()->create(['live_quiz_id' => $quiz->id, 'sort_order' => $i, 'type' => 'multiple', 'question_text' => $text, 'options' => ['Bir', 'İki'], 'correct_answer' => 'A', 'duration_sec' => 20, 'xp' => 10, 'double_xp' => false]);
        }
        $session = LiveQuizSession::query()->create([
            'live_quiz_id' => $quiz->id, 'teacher_user_id' => $teacher->id, 'join_code' => 'QRP001', 'status' => 'finished',
            'current_index' => 1, 'is_locked' => true, 'started_at_ms' => 1000000, 'ends_at_ms' => 1100000, 'finished_at_ms' => 1090000,
        ]);

        foreach ([['Ayşe Demir', ['A', 'A']], ['Can Yılmaz', ['A', 'B']], ['Eda Kara', []]] as $i => [$name, $picks]) {
            $u = User::query()->create(['role_id' => $studentRole->id, 'name' => $name, 'email' => 'qr-s'.$i.'@test.local', 'password' => 'secret123', 'is_active' => true]);
            LiveQuizParticipant::query()->create(['live_quiz_session_id' => $session->id, 'student_user_id' => $u->id, 'joined_at_ms' => 1000000]);
            foreach ($picks as $qi => $pick) {
                LiveQuizAnswer::query()->create([
                    'live_quiz_session_id' => $session->id, 'student_user_id' => $u->id, 'question_index' => $qi,
                    'selected_answer' => $pick, 'is_correct' => $pick === 'A', 'xp_earned' => $pick === 'A' ? 10 : 0, 'answered_at_ms' => 1000500,
                ]);
            }
        }

        $this->actingAs($teacher)->get(route('live-quiz.session.report', $session))
            ->assertOk()->assertSee('Canlı Quiz Raporu')->assertSee('Algoritma Quiz')
            ->assertSee('En zor soru')->assertSee('İkinci soru')->assertSee('Cevap Dağılımı')
            ->assertSee('Ayşe Demir')->assertSee('Eda Kara');

        $csv = $this->actingAs($teacher)->get(route('live-quiz.session.report.csv', $session));
        $csv->assertOk();
        $this->assertStringContainsString('Ayşe Demir', $csv->streamedContent());

        $this->actingAs($other)->get(route('live-quiz.session.report', $session))->assertForbidden();
    }
}
