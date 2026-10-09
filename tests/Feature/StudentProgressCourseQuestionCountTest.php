<?php

namespace Tests\Feature;

use App\Models\ContentProgress;
use App\Models\Course;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\User;
use App\Services\StudentProgressReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentProgressCourseQuestionCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_repairs_legacy_question_total_from_current_course_questions(): void
    {
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $teacherUser = $this->user($teacherRole->id, 'Öğretmen', 'question-teacher@example.test');
        $studentUser = $this->user($studentRole->id, 'Öğrenci', 'question-student@example.test');
        $teacher = Teacher::query()->create(['user_id' => $teacherUser->id, 'branch' => 'Bilişim']);
        $class = SchoolClass::query()->create([
            'name' => '5',
            'section' => 'A',
            'grade_level' => 5,
            'teacher_id' => $teacher->id,
            'academic_year' => '2026-2027',
        ]);
        $student = $studentUser->student()->firstOrFail();
        $student->update(['school_class_id' => $class->id]);

        $slides = collect(range(1, 5))->map(fn (int $number) => [
            'title' => 'Soru '.$number,
            'question_prompt' => 'Soru metni '.$number,
            'interaction_type' => 'multiple_choice',
        ])->all();
        $course = Course::query()->create([
            'name' => 'Beş Soruluk Ders',
            'code' => 'BES-SORU',
            'teacher_id' => $teacher->id,
            'created_by' => $teacherUser->id,
            'school_class_id' => $class->id,
            'lesson_payload' => ['slides' => $slides],
        ]);

        ContentProgress::query()->create([
            'content_id' => 'course-'.$course->id,
            'user_id' => $studentUser->id,
            'completed' => true,
            'xp_awarded' => 132,
            'payload' => [
                'source' => 'course_slide',
                'course_name' => $course->name,
                'question_total' => 6,
                'solved_questions' => 5,
                'correct_questions' => 5,
                'wrong_questions' => 0,
            ],
        ]);

        $courseItem = collect(app(StudentProgressReportService::class)->build($student)['course_items'])
            ->firstWhere('course_name', $course->name);

        $this->assertSame(5, $courseItem['question_total']);
        $this->assertSame(5, $courseItem['correct_questions']);
        $this->assertSame(0, $courseItem['wrong_questions']);

        $this->actingAs($studentUser)
            ->get(route('student.portal.progress'))
            ->assertOk()
            ->assertSee('5 / 5')
            ->assertDontSee('5 / 6');
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
