<?php

namespace Tests\Feature;

use App\Models\ActivityAttempt;
use App\Models\ActivityQuestion;
use App\Models\AttemptAnswer;
use App\Models\CodingActivity;
use App\Models\Course;
use App\Models\CourseHomework;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\StudentHomeworkProgress;
use App\Models\Teacher;
use App\Models\User;
use App\Services\StudentProgressReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompletedStudentHistoryPreservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_daily_activity_stays_in_progress_report_after_activity_is_archived(): void
    {
        $studentUser = $this->studentUser();
        $student = $studentUser->student()->firstOrFail();
        $activity = CodingActivity::query()->create([
            'type' => 'daily_task',
            'title' => 'Günlük Egzersiz',
            'base_xp' => 10,
            'is_active' => true,
        ]);
        $question = ActivityQuestion::query()->create([
            'coding_activity_id' => $activity->id,
            'question_type' => 'single_choice',
            'prompt' => 'Doğru cevap?',
            'answer_key' => ['A'],
            'points' => 10,
            'order_no' => 1,
        ]);
        $attempt = ActivityAttempt::query()->create([
            'coding_activity_id' => $activity->id,
            'user_id' => $studentUser->id,
            'status' => 'finished',
            'score' => 10,
            'submitted_at' => now(),
        ]);
        AttemptAnswer::query()->create([
            'activity_attempt_id' => $attempt->id,
            'activity_question_id' => $question->id,
            'answer_payload' => ['answer' => 'A'],
            'awarded_points' => 10,
        ]);

        $activity->delete();

        $this->assertSoftDeleted($activity);
        $this->assertDatabaseHas('activity_attempts', ['id' => $attempt->id]);
        $this->assertDatabaseHas('attempt_answers', ['activity_attempt_id' => $attempt->id]);
        $report = app(StudentProgressReportService::class)->build($student);
        $this->assertSame(1, $report['kpi']['daily_attempt_count']);
        $this->assertSame(1, $report['kpi']['daily_correct_count']);
    }

    public function test_completed_homework_stays_linked_after_course_and_homework_are_archived(): void
    {
        $studentUser = $this->studentUser();
        $student = $studentUser->student()->firstOrFail();
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $teacherUser = User::query()->create([
            'role_id' => $teacherRole->id,
            'name' => 'Ödev Öğretmeni',
            'email' => 'history-teacher@example.test',
            'password' => 'secret123',
            'is_active' => true,
        ]);
        $teacher = Teacher::query()->create(['user_id' => $teacherUser->id, 'branch' => 'Bilişim']);
        $schoolClass = SchoolClass::query()->create([
            'name' => '7 B',
            'section' => 'B',
            'grade_level' => 7,
            'teacher_id' => $teacher->id,
            'academic_year' => '2026-2027',
        ]);
        $student->update(['school_class_id' => $schoolClass->id]);
        $course = Course::query()->create([
            'name' => 'Arşivlenecek Ders',
            'code' => 'ARSIV-1',
            'teacher_id' => $teacher->id,
            'created_by' => $teacherUser->id,
            'school_class_id' => $schoolClass->id,
        ]);
        $homework = CourseHomework::query()->create([
            'course_id' => $course->id,
            'school_class_id' => $schoolClass->id,
            'assignment_type' => 'course',
            'title' => 'Tamamlanan Ödev',
            'created_by' => $teacherUser->id,
        ]);
        $progress = StudentHomeworkProgress::query()->create([
            'course_homework_id' => $homework->id,
            'student_id' => $student->id,
            'completed_at' => now(),
            'xp_awarded' => 20,
        ]);

        $homework->delete();
        $course->delete();

        $this->assertSoftDeleted($homework);
        $this->assertSoftDeleted($course);
        $this->assertDatabaseHas('student_homework_progresses', ['id' => $progress->id]);
        $this->assertSame('Tamamlanan Ödev', $progress->fresh()->homework->title);
        $this->assertSame('Arşivlenecek Ders', $progress->fresh()->homework->course->name);
    }

    private function studentUser(): User
    {
        $role = Role::query()->firstOrCreate(
            ['slug' => 'student'],
            ['name' => 'Student']
        );

        return User::query()->create([
            'role_id' => $role->id,
            'name' => 'Geçmiş Öğrencisi',
            'email' => uniqid('history-', true).'@example.test',
            'password' => 'secret123',
            'is_active' => true,
        ]);
    }
}
