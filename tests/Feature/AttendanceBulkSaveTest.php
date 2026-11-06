<?php

namespace Tests\Feature;

use App\Models\LessonAttendance;
use App\Models\LessonAttendanceRecord;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceBulkSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_form_saves_every_student_and_page_preselects_current_status(): void
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $admin = User::query()->create(['role_id' => $adminRole->id, 'name' => 'Yonetici', 'email' => 'adm@school.local', 'password' => 'secret123', 'is_active' => true]);
        $tu = User::query()->create(['role_id' => $teacherRole->id, 'name' => 'Ogretmen', 'email' => 'ogr@school.local', 'password' => 'secret123', 'is_active' => true]);
        $teacher = Teacher::query()->create(['user_id' => $tu->id, 'branch' => 'Bilisim']);
        $class = SchoolClass::query()->create(['name' => '5', 'section' => 'A', 'grade_level' => 5, 'teacher_id' => $teacher->id, 'academic_year' => '2026-2027']);
        $other = SchoolClass::query()->create(['name' => '6', 'section' => 'B', 'grade_level' => 6, 'teacher_id' => $teacher->id, 'academic_year' => '2026-2027']);
        $ids = [];
        foreach (['Ali Bir', 'Veli Iki', 'Ayse Uc', 'Can Dort'] as $i => $n) {
            $u = User::query()->create(['role_id' => $studentRole->id, 'name' => $n, 'email' => "o{$i}@school.local", 'password' => 'secret123', 'is_active' => true]);
            $s = $u->student()->firstOrFail();
            $s->update(['school_class_id' => $i === 3 ? $other->id : $class->id, 'student_no' => (string) (700 + $i)]);
            $ids[] = $s->id;
        }
        $lesson = LessonAttendance::query()->create([
            'school_class_id' => $class->id, 'subject' => 'Bilisim', 'date' => now()->toDateString(),
            'start_at' => now()->subHour(), 'end_at' => now()->subMinutes(10), 'finalized_at' => now()->subMinutes(5), 'total_students' => 3, 'present_count' => 0, 'absent_count' => 3,
        ]);
        foreach ([0, 1, 2] as $k) {
            LessonAttendanceRecord::query()->create(['lesson_attendance_id' => $lesson->id, 'student_id' => $ids[$k], 'status' => 'absent', 'source' => 'auto']);
        }

        // sayfa: radyo seçimleri mevcut durumla gelir, Kaydet ve Sınıf Tam var
        $this->actingAs($admin)->get(route('attendance.lesson', $lesson))->assertOk()->assertSee('Sınıf Tam')->assertSee('Kaydet')->assertSee('name="status[' . $ids[0] . ']"', false);

        // Sınıf Tam'ın gönderdiği form: hepsi Geldi (başka sınıftan öğrenci yok sayılır)
        $payload = ['status' => [$ids[0] => 'present', $ids[1] => 'present', $ids[2] => 'present', $ids[3] => 'present']];
        $this->actingAs($admin)->post(route('attendance.lesson.bulk', $lesson), $payload)->assertRedirect(route('attendance.lesson', $lesson))->assertSessionHas('ok');
        $q = fn () => LessonAttendanceRecord::query()->where('lesson_attendance_id', $lesson->id);
        $this->assertSame(3, $q()->where('status', 'present')->where('source', 'manual')->count());
        $this->assertSame(0, $q()->where('student_id', $ids[3])->count());
        $this->assertSame(3, (int) $lesson->fresh()->present_count);
        $this->assertSame(0, (int) $lesson->fresh()->absent_count);

        // karışık seçim
        $this->actingAs($admin)->post(route('attendance.lesson.bulk', $lesson), ['status' => [$ids[0] => 'absent', $ids[1] => 'excused', $ids[2] => 'present']])->assertSessionHasNoErrors();
        $this->assertSame('absent', $q()->where('student_id', $ids[0])->value('status'));
        $this->assertSame('excused', $q()->where('student_id', $ids[1])->value('status'));
        $this->assertSame(1, (int) $lesson->fresh()->present_count);

        // geçersiz değer reddedilir
        $this->actingAs($admin)->post(route('attendance.lesson.bulk', $lesson), ['status' => [$ids[0] => 'hack']])->assertSessionHasErrors('status.*');
    }
}
