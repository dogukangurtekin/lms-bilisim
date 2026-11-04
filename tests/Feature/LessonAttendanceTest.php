<?php

namespace Tests\Feature;

use App\Models\AttendanceOffDay;
use App\Models\LessonAttendance;
use App\Models\LessonAttendanceRecord;
use App\Models\LessonSlot;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\LessonAttendanceService as Att;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LessonAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $teacher;
    private User $otherTeacher;
    private SchoolClass $classA;
    private SchoolClass $classB;
    /** @var Student[] */
    private array $students = [];

    protected function setUp(): void
    {
        parent::setUp();
        // Pazartesi 08:00: program bu andan önce kurulur (geriye dönük devamsızlık yazılmasın).
        Carbon::setTestNow(Carbon::parse('2026-11-09 08:00:00'));

        $ar = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $tr = Role::query()->firstOrCreate(['slug' => 'teacher'], ['name' => 'Teacher']);
        $sr = Role::query()->firstOrCreate(['slug' => 'student'], ['name' => 'Student']);
        $this->admin = User::query()->create(['role_id' => $ar->id, 'name' => 'Yönetici', 'email' => 'ad@t.l', 'password' => 'secret123', 'is_active' => true]);
        $this->teacher = User::query()->create(['role_id' => $tr->id, 'name' => 'Hoca A', 'email' => 'ha@okul.com', 'password' => 'secret123', 'is_active' => true]);
        $this->otherTeacher = User::query()->create(['role_id' => $tr->id, 'name' => 'Hoca B', 'email' => 'hb@okul.com', 'password' => 'secret123', 'is_active' => true]);
        $t1 = Teacher::query()->updateOrCreate(['user_id' => $this->teacher->id], []);
        $t2 = Teacher::query()->updateOrCreate(['user_id' => $this->otherTeacher->id], []);
        $this->classA = SchoolClass::query()->create(['name' => '5', 'section' => 'A', 'grade_level' => 5, 'teacher_id' => $t1->id, 'academic_year' => '2026-2027']);
        $this->classB = SchoolClass::query()->create(['name' => '6', 'section' => 'B', 'grade_level' => 6, 'teacher_id' => $t2->id, 'academic_year' => '2026-2027']);

        foreach (['Ali Yıldız' => $this->classA, 'Ayşe Demir' => $this->classA, 'Can Kaya' => $this->classA, 'Bora Çelik' => $this->classB] as $name => $class) {
            $u = User::query()->create(['role_id' => $sr->id, 'name' => $name, 'email' => md5($name) . '@t.l', 'password' => 'secret123', 'is_active' => true]);
            $this->students[$name] = Student::query()->updateOrCreate(['user_id' => $u->id], ['student_no' => 'N' . count($this->students), 'school_class_id' => $class->id]);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function slot(SchoolClass $c, string $start = '09:00:00', string $end = '09:40:00', int $weekday = 1): LessonSlot
    {
        return LessonSlot::query()->create(['school_class_id' => $c->id, 'weekday' => $weekday, 'start_time' => $start, 'end_time' => $end, 'subject' => 'Bilişim Teknolojileri']);
    }

    private function at(string $time): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-09 ' . $time));
    }

    private function statusOf(string $name): ?string
    {
        $lesson = LessonAttendance::query()->where('school_class_id', $this->students[$name]->school_class_id)->first();

        return $lesson ? LessonAttendanceRecord::query()->where('lesson_attendance_id', $lesson->id)->where('student_id', $this->students[$name]->id)->value('status') : null;
    }

    // ───────────── otomatik yoklama ─────────────

    public function test_students_seen_during_the_lesson_are_present_and_the_rest_become_absent_when_it_ends(): void
    {
        $this->slot($this->classA);
        $this->at('09:05:00');
        Att::noteStudentSeen($this->students['Ali Yıldız']->id);
        $this->at('09:20:00');
        Att::noteStudentSeen($this->students['Ayşe Demir']->id);

        // ders sürerken kapatma yapılmaz
        $this->assertSame(0, Att::finalizeDue());

        $this->at('09:41:00');
        $this->assertSame(1, Att::finalizeDue());
        $this->assertSame('present', $this->statusOf('Ali Yıldız'));
        $this->assertSame('present', $this->statusOf('Ayşe Demir'));
        $this->assertSame('absent', $this->statusOf('Can Kaya'));
        $this->assertNull($this->statusOf('Bora Çelik'), 'başka sınıfın öğrencisi bu derse yazılmaz');

        $lesson = LessonAttendance::query()->firstOrFail();
        $this->assertSame([3, 2, 1], [$lesson->total_students, $lesson->present_count, $lesson->absent_count]);
        $this->assertNotNull($lesson->finalized_at);
        $this->assertSame(0, Att::finalizeDue(), 'ikinci kez kapatılmaz');
    }

    public function test_early_grace_counts_but_a_login_after_the_lesson_does_not(): void
    {
        $this->slot($this->classA);
        $this->at('08:40:00');
        Att::noteStudentSeen($this->students['Ali Yıldız']->id);          // 20 dk erken: sayılmaz
        $this->at('08:52:00');
        Att::noteStudentSeen($this->students['Ayşe Demir']->id);          // 8 dk erken: sayılır
        $this->at('09:50:00');
        Att::noteStudentSeen($this->students['Can Kaya']->id);            // ders bitti: sayılmaz
        Att::finalizeDue();

        $this->assertSame('absent', $this->statusOf('Ali Yıldız'));
        $this->assertSame('present', $this->statusOf('Ayşe Demir'));
        $this->assertSame('absent', $this->statusOf('Can Kaya'));
    }

    public function test_no_backfill_for_lessons_that_started_before_the_schedule_was_added(): void
    {
        $this->at('09:30:00');
        $this->slot($this->classA);                                       // ders 09:00'da başlamıştı, program 09:30'da eklendi
        $this->at('10:00:00');
        $this->assertSame(0, Att::finalizeDue());
        $this->assertSame(0, LessonAttendanceRecord::query()->count());
    }

    public function test_off_days_take_no_attendance(): void
    {
        $this->slot($this->classA);
        AttendanceOffDay::query()->create(['date' => '2026-11-09', 'note' => 'Tatil']);
        Att::bust();
        $this->at('09:10:00');
        Att::noteStudentSeen($this->students['Ali Yıldız']->id);
        $this->at('09:45:00');
        $this->assertSame(0, Att::finalizeDue());
        $this->assertSame(0, LessonAttendanceRecord::query()->count());
    }

    public function test_student_requests_mark_presence_through_the_tracking_middleware(): void
    {
        $this->slot($this->classA);
        $this->at('09:12:00');
        $student = $this->students['Ali Yıldız'];

        $this->actingAs($student->user)->get(route('student.portal.dashboard'));

        $this->at('09:45:00');
        Att::finalizeDue();
        $this->assertSame('present', $this->statusOf('Ali Yıldız'));
        $this->assertSame('absent', $this->statusOf('Ayşe Demir'));
    }

    public function test_catch_up_closes_lessons_missed_while_nothing_was_running(): void
    {
        $this->slot($this->classA);                                       // pazartesi
        $this->at('09:10:00');
        Att::noteStudentSeen($this->students['Ali Yıldız']->id);
        Carbon::setTestNow(Carbon::parse('2026-11-10 10:00:00'));         // ertesi gün ilk çalışma
        $this->assertSame(1, Att::finalizeDue());
        $this->assertSame('present', $this->statusOf('Ali Yıldız'));
        $this->assertSame('absent', $this->statusOf('Can Kaya'));
    }

    // ───────────── ders programı: şablon ve içe aktarma ─────────────

    public function test_time_and_day_parsing_accepts_common_excel_forms(): void
    {
        $this->assertSame('09:00:00', Att::parseTime('09:00'));
        $this->assertSame('09:30:00', Att::parseTime('9.30'));
        $this->assertSame('09:00:00', Att::parseTime('0.375'));
        $this->assertSame('13:10:00', Att::parseTime('13:10:00'));
        $this->assertNull(Att::parseTime('25:00'));
        $this->assertNull(Att::parseTime('abc'));
        $this->assertSame(1, Att::parseWeekday('Pazartesi'));
        $this->assertSame(3, Att::parseWeekday('çarşamba'));
        $this->assertSame(4, Att::parseWeekday('PERŞEMBE'));
        $this->assertSame(5, Att::parseWeekday('5'));
        $this->assertNull(Att::parseWeekday('Funday'));
    }

    public function test_template_can_be_downloaded_and_a_filled_file_defines_the_schedule(): void
    {
        $this->actingAs($this->admin)->get(route('attendance.schedule.template'))->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $csv = "Sınıf;Şube;Gün;Ders No;Başlangıç;Bitiş;Ders Adı;Öğretmen\n5;A;Pazartesi;1;08:30;09:10;Bilişim Teknolojileri;\n5;A;Çarşamba;3;10:20;11:00;;ha@okul.com\n6;b;Salı;2;09:20;10:00;;\n";
        $this->actingAs($this->admin)->post(route('attendance.schedule.import'), ['file' => UploadedFile::fake()->createWithContent('program.csv', $csv), 'replace' => '1'])
            ->assertRedirect()->assertSessionHas('ok');

        $this->assertSame(2, LessonSlot::query()->where('school_class_id', $this->classA->id)->count());
        $this->assertSame(1, LessonSlot::query()->where('school_class_id', $this->classB->id)->count());
        $wed = LessonSlot::query()->where('weekday', 3)->firstOrFail();
        $this->assertSame('Bilişim Teknolojileri', $wed->subject);
        $this->assertSame($this->teacher->id, (int) $wed->teacher_user_id);
        $this->assertSame('10:20:00', (string) $wed->start_time);
    }

    public function test_a_file_with_any_error_saves_nothing_and_lists_the_lines(): void
    {
        $bad = "Sınıf;Şube;Gün;Ders No;Başlangıç;Bitiş;Ders Adı;Öğretmen\n5;A;Pazartesi;1;08:30;09:10;;\n9;Z;Salı;1;08:30;09:10;;\n5;A;Cuma;1;10:00;09:00;;\n5;A;Funday;1;08:30;09:10;;\n";
        $r = $this->actingAs($this->admin)->post(route('attendance.schedule.import'), ['file' => UploadedFile::fake()->createWithContent('bad.csv', $bad)]);
        $r->assertRedirect()->assertSessionHas('err')->assertSessionHas('import_errors');
        $errors = session('import_errors');
        $this->assertTrue(collect($errors)->contains(fn ($e) => str_contains($e, 'Satır 3') && str_contains($e, 'sınıf bulunamadı')));
        $this->assertTrue(collect($errors)->contains(fn ($e) => str_contains($e, 'Satır 4') && str_contains($e, 'bitiş')));
        $this->assertTrue(collect($errors)->contains(fn ($e) => str_contains($e, 'Satır 5') && str_contains($e, 'gün')));
        $this->assertSame(0, LessonSlot::query()->count(), 'hatalı dosyada satır kısmen bile kaydedilmez');
    }

    public function test_overlapping_lessons_in_a_file_are_rejected(): void
    {
        $csv = "Sınıf;Şube;Gün;Ders No;Başlangıç;Bitiş;Ders Adı;Öğretmen\n5;A;Pazartesi;1;08:30;09:10;;\n5;A;Pazartesi;2;09:00;09:40;;\n";
        $this->actingAs($this->admin)->post(route('attendance.schedule.import'), ['file' => UploadedFile::fake()->createWithContent('o.csv', $csv)])->assertSessionHas('import_errors');
        $this->assertSame(0, LessonSlot::query()->count());
    }

    public function test_replace_keeps_unchanged_lessons_so_todays_attendance_rules_are_not_reset(): void
    {
        $keep = $this->slot($this->classA, '09:00:00', '09:40:00');
        $drop = $this->slot($this->classA, '10:00:00', '10:40:00');
        $createdAt = $keep->created_at->toDateTimeString();
        $this->at('12:00:00');

        $csv = "Sınıf;Şube;Gün;Ders No;Başlangıç;Bitiş;Ders Adı;Öğretmen\n5;A;Pazartesi;1;09:00;09:40;Yeni Ad;\n5;A;Salı;1;09:00;09:40;;\n";
        $this->actingAs($this->admin)->post(route('attendance.schedule.import'), ['file' => UploadedFile::fake()->createWithContent('r.csv', $csv), 'replace' => '1'])->assertSessionHas('ok');

        $keep->refresh();
        $this->assertSame($createdAt, $keep->created_at->toDateTimeString());
        $this->assertSame('Yeni Ad', $keep->subject);
        $this->assertNull(LessonSlot::query()->find($drop->id));
        $this->assertSame(2, LessonSlot::query()->where('school_class_id', $this->classA->id)->count());
    }

    // ───────────── yetki ve sayfalar ─────────────

    public function test_teacher_sees_only_own_classes_and_cannot_touch_other_classes(): void
    {
        $this->slot($this->classA);
        $this->slot($this->classB);
        $this->at('09:10:00');
        Att::noteStudentSeen($this->students['Ali Yıldız']->id);
        Att::noteStudentSeen($this->students['Bora Çelik']->id);
        $this->at('09:45:00');
        Att::finalizeDue();
        $lessonA = LessonAttendance::query()->where('school_class_id', $this->classA->id)->firstOrFail();
        $lessonB = LessonAttendance::query()->where('school_class_id', $this->classB->id)->firstOrFail();

        $this->actingAs($this->teacher)->get(route('attendance.index', ['tab' => 'history']))->assertOk()->assertSee('5/A')->assertDontSee('6/B');
        $this->actingAs($this->teacher)->get(route('attendance.lesson', $lessonA))->assertOk()->assertSee('Ali Yıldız');
        $this->actingAs($this->teacher)->get(route('attendance.lesson', $lessonB))->assertForbidden();
        $this->actingAs($this->teacher)->post(route('attendance.lesson.status', [$lessonB, $this->students['Bora Çelik']]), ['status' => 'absent'])->assertForbidden();
        $this->actingAs($this->teacher)->post(route('attendance.schedule.store'), [
            'school_class_id' => $this->classB->id, 'weekday' => 2, 'start_time' => '09:00', 'end_time' => '09:40',
        ])->assertForbidden();

        $csv = "Sınıf;Şube;Gün;Ders No;Başlangıç;Bitiş;Ders Adı;Öğretmen\n6;B;Salı;1;09:00;09:40;;\n";
        $this->actingAs($this->teacher)->post(route('attendance.schedule.import'), ['file' => UploadedFile::fake()->createWithContent('t.csv', $csv)])->assertSessionHas('import_errors');

        $this->actingAs($this->admin)->get(route('attendance.lesson', $lessonB))->assertOk();
    }

    public function test_students_cannot_open_the_attendance_pages(): void
    {
        $student = $this->students['Ali Yıldız']->user;
        $this->actingAs($student)->get(route('attendance.index'))->assertForbidden();
        $this->actingAs($student)->get(route('attendance.widget'))->assertForbidden();
    }

    public function test_manual_correction_updates_status_and_counts_and_history_keeps_it(): void
    {
        $this->slot($this->classA);
        $this->at('09:45:00');
        Att::finalizeDue();
        $lesson = LessonAttendance::query()->firstOrFail();
        $this->assertSame(3, (int) $lesson->absent_count);

        $this->actingAs($this->admin)->post(route('attendance.lesson.status', [$lesson, $this->students['Can Kaya']]), ['status' => 'excused'])->assertRedirect();
        $this->actingAs($this->admin)->post(route('attendance.lesson.status', [$lesson, $this->students['Ali Yıldız']]), ['status' => 'present'])->assertRedirect();

        $lesson->refresh();
        $this->assertSame([1, 1], [(int) $lesson->present_count, (int) $lesson->absent_count]);
        $rec = LessonAttendanceRecord::query()->where('student_id', $this->students['Can Kaya']->id)->firstOrFail();
        $this->assertSame(['excused', 'manual'], [$rec->status, $rec->source]);
    }

    public function test_pages_widget_export_and_history_filters(): void
    {
        $this->slot($this->classA);
        $this->slot($this->classB, '10:00:00', '10:40:00');
        $this->at('09:10:00');
        Att::noteStudentSeen($this->students['Ali Yıldız']->id);
        $this->at('09:45:00');
        Att::finalizeDue();
        $this->at('10:20:00');                                            // 6/B dersi sürüyor

        $this->actingAs($this->admin)->get(route('attendance.index'))->assertOk()->assertSee('Bugünün Dersleri')->assertSee('5/A')->assertSee('6/B')->assertSee('Ayşe Demir');
        $this->actingAs($this->admin)->get(route('attendance.widget'))->assertOk()->assertSee('Bugünkü Yoklama')->assertSee('5/A')->assertSee('Ayşe Demir');
        $this->actingAs($this->admin)->get(route('attendance.index', ['tab' => 'history', 'from' => '2026-11-01', 'to' => '2026-11-30', 'class_id' => $this->classA->id]))
            ->assertOk()->assertSee('<td>5/A</td>', false)->assertDontSee('<td>6/B</td>', false)->assertSee('En Çok Devamsızlık Yapanlar');
        $this->actingAs($this->admin)->get(route('attendance.schedule'))->assertOk()->assertSee('Şablonu İndir')->assertSee('Tatil');

        $csv = $this->actingAs($this->admin)->get(route('attendance.export', ['from' => '2026-11-01', 'to' => '2026-11-30']))->assertOk()->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Ali Yıldız', $csv);
        $this->assertStringContainsString('Gelmedi', $csv);
    }

    public function test_dashboard_right_panel_has_the_attendance_widget_for_staff(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))->assertOk()->assertSee('id="attWidget"', false)->assertSee(route('attendance.widget'), false);
    }

    public function test_slot_form_validates_and_rejects_overlaps_and_off_days_are_admin_only(): void
    {
        $this->slot($this->classA, '09:00:00', '09:40:00');
        $base = ['school_class_id' => $this->classA->id, 'weekday' => 1, 'subject' => 'BT'];
        $this->actingAs($this->admin)->post(route('attendance.schedule.store'), $base + ['start_time' => '09:30', 'end_time' => '10:10'])->assertSessionHas('err');
        $this->actingAs($this->admin)->post(route('attendance.schedule.store'), $base + ['start_time' => '10:00', 'end_time' => '09:00'])->assertSessionHasErrors('end_time');
        $this->actingAs($this->admin)->post(route('attendance.schedule.store'), $base + ['start_time' => '10:00', 'end_time' => '10:40'])->assertSessionHas('ok');
        $this->assertSame(2, LessonSlot::query()->count());

        $this->actingAs($this->teacher)->post(route('attendance.offday.store'), ['date' => '2026-11-10'])->assertForbidden();
        $this->actingAs($this->admin)->post(route('attendance.offday.store'), ['date' => '2026-11-10', 'note' => 'Tatil'])->assertRedirect();
        $this->assertSame(1, AttendanceOffDay::query()->count());
    }
}
