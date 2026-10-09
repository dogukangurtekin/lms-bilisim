<?php

namespace Tests\Feature;

use App\Models\ContentProgress;
use App\Models\LiveQuiz;
use App\Models\LiveQuizAnswer;
use App\Models\LiveQuizSession;
use App\Models\Role;
use App\Models\StudentReport;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\StudentProgressReportService;
use App\Services\StudentXpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentXpConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_xp_is_identical_in_student_data_dashboard_and_progress_report(): void
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $admin = $this->user($adminRole->id, 'Admin', 'xp-admin@example.test');
        $studentUser = $this->user($studentRole->id, 'Ali Yayla', 'ali-yayla@example.test');
        $student = $studentUser->student()->firstOrFail();

        ContentProgress::query()->create([
            'content_id' => 'xp-consistency-source',
            'user_id' => $studentUser->id,
            'completed' => true,
            'xp_awarded' => 1040,
        ]);

        $quiz = LiveQuiz::query()->create([
            'teacher_user_id' => $admin->id,
            'title' => 'XP tutarlilik testi',
            'status' => 'active',
        ]);
        $session = LiveQuizSession::query()->create([
            'live_quiz_id' => $quiz->id,
            'teacher_user_id' => $admin->id,
            'join_code' => 'XPSYNC',
            'status' => 'finished',
        ]);
        LiveQuizAnswer::query()->create([
            'live_quiz_session_id' => $session->id,
            'student_user_id' => $studentUser->id,
            'question_index' => 0,
            'selected_answer' => 'A',
            'is_correct' => true,
            'xp_earned' => 24,
        ]);
        UserProfile::query()->updateOrCreate(
            ['user_id' => $studentUser->id],
            ['role' => 'student', 'xp' => 1064],
        );

        $xpService = app(StudentXpService::class);
        $this->assertSame(1064, $xpService->earned($student));
        $this->assertSame(1064, $xpService->available($student));
        $this->assertSame(1064, app(StudentProgressReportService::class)->build($student)['kpi']['total_xp']);

        $this->actingAs($admin)
            ->get(route('student-data.index', ['list' => 1, 'q' => 'Ali Yayla']))
            ->assertOk()
            ->assertSee('Ali Yayla')
            ->assertSee('1064');

        $this->actingAs($admin)
            ->getJson(route('dashboard.ranking-by-class'))
            ->assertOk()
            ->assertJsonPath('students.0.xp', 1064);
    }

    public function test_preserved_student_report_restores_a_profile_xp_that_was_previously_lowered(): void
    {
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $studentUser = $this->user($studentRole->id, 'XP Koruma', 'xp-guard@example.test');
        $student = $studentUser->student()->firstOrFail();

        $profile = UserProfile::query()->updateOrCreate(
            ['user_id' => $studentUser->id],
            ['role' => 'student', 'xp' => 1700],
        );
        StudentReport::query()->create([
            'user_id' => $studentUser->id,
            'total_xp' => 4200,
        ]);

        $this->assertSame(4200, app(StudentXpService::class)->earned($student));

        $profile->xp = 0;
        $profile->save();
        $this->assertSame(1700, (int) $profile->fresh()->xp);
        $this->assertSame(4200, app(StudentXpService::class)->available($student));
    }

    private function user(int $roleId, string $name, string $email): User
    {
        return User::query()->create([
            'role_id' => $roleId,
            'name' => $name,
            'email' => $email,
            'password' => 'secret123',
            'is_active' => true,
        ]);
    }
}
