<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentMessage;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentMessageDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $teacher;
    private User $otherTeacher;
    private User $studentUser;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        $ar = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $tr = Role::query()->firstOrCreate(['slug' => 'teacher'], ['name' => 'Teacher']);
        $sr = Role::query()->firstOrCreate(['slug' => 'student'], ['name' => 'Student']);
        $this->admin = User::query()->create(['role_id' => $ar->id, 'name' => 'Yönetici', 'email' => 'ad@t.l', 'password' => 'secret123', 'is_active' => true]);
        $this->teacher = User::query()->create(['role_id' => $tr->id, 'name' => 'Öğretmen A', 'email' => 'ta@t.l', 'password' => 'secret123', 'is_active' => true]);
        $this->otherTeacher = User::query()->create(['role_id' => $tr->id, 'name' => 'Öğretmen B', 'email' => 'tb@t.l', 'password' => 'secret123', 'is_active' => true]);
        $t = Teacher::query()->updateOrCreate(['user_id' => $this->teacher->id], []);
        $class = SchoolClass::query()->create(['name' => '5-A', 'section' => 'A', 'grade_level' => 5, 'teacher_id' => $t->id, 'academic_year' => '2026-2027']);
        $this->studentUser = User::query()->create(['role_id' => $sr->id, 'name' => 'Ali', 'email' => 'ali@t.l', 'password' => 'secret123', 'is_active' => true]);
        $this->student = Student::query()->updateOrCreate(['user_id' => $this->studentUser->id], ['student_no' => 'N1', 'school_class_id' => $class->id]);
    }

    private function message(array $o = []): StudentMessage
    {
        return StudentMessage::query()->create($o + [
            'student_id' => $this->student->id, 'student_user_id' => $this->studentUser->id,
            'recipient_type' => 'teacher', 'recipient_user_id' => $this->teacher->id, 'body' => 'Merhaba hocam, bir sorum var.',
        ]);
    }

    public function test_unanswered_message_deleted_by_teacher_disappears_from_student_and_unblocks_sending(): void
    {
        $m = $this->message();
        $this->actingAs($this->studentUser)->get(route('student.messages.index'))->assertSee('Merhaba hocam, bir sorum var.')->assertSee('Cevap bekleniyor');

        $this->actingAs($this->teacher)->delete(route('staff.messages.destroy', $m))->assertRedirect();

        $this->actingAs($this->studentUser)->get(route('student.messages.index'))
            ->assertDontSee('Merhaba hocam, bir sorum var.')->assertDontSee('Cevap bekleniyor');
        $this->assertFalse(StudentMessage::hasPendingFor($this->student->id));
    }

    public function test_unanswered_message_deleted_by_admin_also_disappears_from_student(): void
    {
        $m = $this->message(['recipient_type' => 'admin', 'recipient_user_id' => null]);
        $this->actingAs($this->admin)->delete(route('staff.messages.destroy', $m))->assertRedirect();
        $this->actingAs($this->studentUser)->get(route('student.messages.index'))->assertDontSee('Merhaba hocam, bir sorum var.');
    }

    public function test_answered_message_stays_in_student_history_after_staff_deletes_it(): void
    {
        $m = $this->message(['reply_body' => 'Tabii, yardımcı olurum.', 'replied_by' => $this->teacher->id, 'replied_at' => now()]);
        $this->actingAs($this->teacher)->delete(route('staff.messages.destroy', $m))->assertRedirect();
        $this->actingAs($this->studentUser)->get(route('student.messages.index'))->assertSee('Tabii, yardımcı olurum.');
    }

    public function test_teacher_cannot_delete_someone_elses_message_and_student_cannot_delete(): void
    {
        $m = $this->message();
        $this->actingAs($this->otherTeacher)->delete(route('staff.messages.destroy', $m))->assertForbidden();
        $this->actingAs($this->studentUser)->delete(route('staff.messages.destroy', $m))->assertForbidden();
        $this->assertNull($m->fresh()->staff_deleted_at);
    }
}
