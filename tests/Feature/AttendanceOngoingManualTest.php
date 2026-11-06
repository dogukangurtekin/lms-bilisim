<?php

namespace Tests\Feature;

use App\Models\LessonAttendance;
use App\Models\LessonAttendanceRecord;
use App\Models\LessonSlot;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\User;
use App\Services\LessonAttendanceService as Att;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceOngoingManualTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_attendance_works_during_lesson_without_anyone_logging_in_and_survives_finalize(): void
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $admin = User::query()->create(['role_id' => $adminRole->id, 'name' => 'Yonetici', 'email' => 'adm@school.local', 'password' => 'secret123', 'is_active' => true]);
        $tu = User::query()->create(['role_id' => $teacherRole->id, 'name' => 'Ogretmen', 'email' => 'ogr@school.local', 'password' => 'secret123', 'is_active' => true]);
        $teacher = Teacher::query()->create(['user_id' => $tu->id, 'branch' => 'Bilisim']);
        $class = SchoolClass::query()->create(['name' => '5', 'section' => 'A', 'grade_level' => 5, 'teacher_id' => $teacher->id, 'academic_year' => '2026-2027']);
        $students = [];
        foreach (['Ali Bir', 'Veli Iki', 'Ayse Uc'] as $i => $n) {
            $u = User::query()->create(['role_id' => $studentRole->id, 'name' => $n, 'email' => "o{$i}@school.local", 'password' => 'secret123', 'is_active' => true]);
            $s = $u->student()->firstOrFail();
            $s->update(['school_class_id' => $class->id, 'student_no' => (string) (800 + $i)]);
            $students[] = $s;
        }

        // ders şu an sürüyor: slot 10 dk önce başladı, 30 dk sonra bitiyor
        $now = Carbon::now()->startOfMinute();
        Carbon::setTestNow($now);
        $slot = LessonSlot::query()->create([
            'school_class_id' => $class->id, 'weekday' => $now->isoWeekday(), 'lesson_no' => 1, 'subject' => 'Bilisim',
            'start_time' => $now->copy()->subMinutes(10)->format('H:i:s'), 'end_time' => $now->copy()->addMinutes(30)->format('H:i:s'), 'is_active' => true,
        ]);
        $slot->forceFill(['created_at' => $now->copy()->subDays(2)])->save();

        $this->assertSame(0, LessonAttendance::query()->count());   // kimse girmedi, ders kaydı yok

        // "Yoklama Al" dersi açar ve yoklama sayfasına götürür
        $res = $this->actingAs($admin)->post(route('attendance.slot.open', $slot));
        $lesson = LessonAttendance::query()->firstOrFail();
        $res->assertRedirect(route('attendance.lesson', $lesson));

        // kaydı olmayan öğrenciler de listelenir
        $this->actingAs($admin)->get(route('attendance.lesson', $lesson))->assertOk()->assertSee('Ali Bir')->assertSee('Veli Iki')->assertSee('Ayse Uc')->assertSee('Henüz girmedi');

        // bir öğrenci kendi girişiyle (otomatik) Geldi olur
        Att::noteStudentSeen($students[0]->id, $now);
        $this->assertSame('present', LessonAttendanceRecord::query()->where('student_id', $students[0]->id)->value('status'));

        // sınava giden öğrenciler elle işaretlenir: biri Geldi, biri İzinli
        $this->actingAs($admin)->post(route('attendance.lesson.status', [$lesson, $students[1]->id]), ['status' => 'present'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('attendance.lesson.status', [$lesson, $students[2]->id]), ['status' => 'excused'])->assertSessionHasNoErrors();

        // ders biter: elle işaretlenenler değişmez, yeni "Gelmedi" eklenmez
        Carbon::setTestNow($now->copy()->addMinutes(40));
        Att::finalizeDue(Carbon::now());
        $st = fn ($i) => LessonAttendanceRecord::query()->where('lesson_attendance_id', $lesson->id)->where('student_id', $students[$i]->id)->value('status');
        $this->assertSame('present', $st(0));
        $this->assertSame('present', $st(1));
        $this->assertSame('excused', $st(2));
        $this->assertSame(2, (int) $lesson->fresh()->present_count);
        $this->assertSame(0, (int) $lesson->fresh()->absent_count);
        Carbon::setTestNow();
    }
}
