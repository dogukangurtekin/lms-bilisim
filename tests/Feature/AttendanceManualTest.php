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

class AttendanceManualTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_status_buttons_and_class_full_button(): void
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $admin = User::query()->create(['role_id' => $adminRole->id, 'name' => 'Yonetici', 'email' => 'adm@school.local', 'password' => 'secret123', 'is_active' => true]);
        $tu = User::query()->create(['role_id' => $teacherRole->id, 'name' => 'Ogretmen', 'email' => 'ogr@school.local', 'password' => 'secret123', 'is_active' => true]);
        $teacher = Teacher::query()->create(['user_id' => $tu->id, 'branch' => 'Bilisim']);
        $class = SchoolClass::query()->create(['name' => '5', 'section' => 'A', 'grade_level' => 5, 'teacher_id' => $teacher->id, 'academic_year' => '2026-2027']);
        $students = [];
        foreach (['Ali Bir', 'Veli Iki', 'Ayse Uc', 'Can Dort'] as $i => $n) {
            $u = User::query()->create(['role_id' => $studentRole->id, 'name' => $n, 'email' => "o{$i}@school.local", 'password' => 'secret123', 'is_active' => true]);
            $s = $u->student()->firstOrFail();
            $s->update(['school_class_id' => $class->id, 'student_no' => (string) (900 + $i)]);
            $students[] = $s;
        }
        $lesson = LessonAttendance::query()->create([
            'school_class_id' => $class->id, 'subject' => 'Bilisim', 'date' => now()->toDateString(),
            'start_at' => now()->subMinutes(10), 'end_at' => now()->addMinutes(30), 'total_students' => 0, 'present_count' => 0, 'absent_count' => 0,
        ]);
        // ilk iki öğrencinin kaydı var (biri gelmedi, biri izinli); diğer ikisinin kaydı yok
        LessonAttendanceRecord::query()->create(['lesson_attendance_id' => $lesson->id, 'student_id' => $students[0]->id, 'status' => 'absent', 'source' => 'auto']);
        LessonAttendanceRecord::query()->create(['lesson_attendance_id' => $lesson->id, 'student_id' => $students[1]->id, 'status' => 'excused', 'source' => 'manual']);

        // tek öğrenci: Gelmedi -> Geldi
        $this->actingAs($admin)->post(route('attendance.lesson.status', [$lesson, $students[0]->id]), ['status' => 'present'])->assertSessionHasNoErrors();
        $this->assertSame('present', LessonAttendanceRecord::query()->where('student_id', $students[0]->id)->value('status'));
        $this->actingAs($admin)->post(route('attendance.lesson.status', [$lesson, $students[0]->id]), ['status' => 'absent'])->assertSessionHasNoErrors();

        // sınıf tam: gelmedi olan + kaydı olmayanlar Geldi olur, izinli kalır
        $this->actingAs($admin)->post(route('attendance.lesson.all-present', $lesson))->assertSessionHas('ok');
        $st = fn ($i) => LessonAttendanceRecord::query()->where('lesson_attendance_id', $lesson->id)->where('student_id', $students[$i]->id)->value('status');
        $this->assertSame('present', $st(0));
        $this->assertSame('excused', $st(1));
        $this->assertSame('present', $st(2));
        $this->assertSame('present', $st(3));
        $fresh = $lesson->fresh();
        $this->assertSame(4, (int) $fresh->total_students);
        $this->assertSame(3, (int) $fresh->present_count);

        // sayfalar açılır, düğmeler görünür
        $this->actingAs($admin)->get(route('attendance.lesson', $lesson))->assertOk()->assertSee('Sınıf Tam')->assertSee('Gelmedi');
    }
}
