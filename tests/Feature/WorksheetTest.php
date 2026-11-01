<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorksheetTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);

        return User::query()->create([
            'role_id' => $role->id, 'name' => 'Ws Admin', 'email' => 'ws-admin@example.test',
            'password' => 'secret123', 'is_active' => true,
        ]);
    }

    private function form(array $extra = []): array
    {
        return $extra + ['title' => 'Algoritma Çalışma Kâğıdı', 'template' => 'plain', 'columns' => 1];
    }

    public function test_worksheet_is_created_with_answer_mode_and_listed_only_in_its_tab(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('exams.store'), $this->form(['kind' => 'worksheet', 'answer_mode' => 'key_end', 'subject' => 'Bilişim Teknolojileri ve Yazılım']))
            ->assertRedirect();
        $this->actingAs($admin)->post(route('exams.store'), $this->form(['title' => 'Normal Sınav']));

        $ws = Exam::query()->where('kind', 'worksheet')->firstOrFail();
        $this->assertSame('key_end', $ws->settings['answer_mode']);
        $this->assertFalse($ws->settings['show_points']);
        $this->assertNull($ws->duration_min);
        $this->assertSame(1, Exam::query()->where('kind', 'exam')->count());

        $this->actingAs($admin)->get(route('exams.index', ['tab' => 'worksheet']))
            ->assertOk()->assertSee('Algoritma Çalışma Kâğıdı')->assertDontSee('Normal Sınav');
        $this->actingAs($admin)->get(route('exams.index'))
            ->assertOk()->assertSee('Normal Sınav')->assertDontSee('Algoritma Çalışma Kâğıdı');
    }

    public function test_worksheet_pdf_includes_answers_by_default_and_editor_shows_worksheet_labels(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('exams.store'), $this->form(['kind' => 'worksheet', 'answer_mode' => 'inline']));
        $ws = Exam::query()->where('kind', 'worksheet')->firstOrFail();
        ExamQuestion::query()->create([
            'exam_id' => $ws->id, 'sort_order' => 0, 'type' => 'multiple_choice', 'body' => 'En küçük asal sayı kaçtır?',
            'options' => ['1', '2', '3', '4'], 'answer' => 'B', 'points' => 0,
        ]);

        $this->actingAs($admin)->get(route('exams.edit', $ws))
            ->assertOk()->assertSee('Çalışma Kâğıdı Ayarları')->assertSee('Cevap gösterimi');

        $res = $this->actingAs($admin)->get(route('exams.pdf', $ws));
        $res->assertOk();
        $this->assertStringContainsString('cevapli', (string) $res->headers->get('content-disposition'));

        $none = $this->actingAs($admin)->get(route('exams.pdf', ['exam' => $ws, 'mode' => 'student']));
        $this->assertStringContainsString('cevapsiz', (string) $none->headers->get('content-disposition'));
    }

    public function test_exam_creation_is_unchanged_and_invalid_kind_is_rejected(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('exams.store'), $this->form(['duration_min' => 40]))->assertRedirect();
        $exam = Exam::query()->firstOrFail();
        $this->assertSame('exam', $exam->kind);
        $this->assertSame(40, (int) $exam->duration_min);

        $this->actingAs($admin)->post(route('exams.store'), $this->form(['kind' => 'bogus']))->assertSessionHasErrors('kind');
    }

    public function test_worksheet_can_be_assigned_as_homework_with_public_link(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('exams.store'), $this->form(['kind' => 'worksheet', 'answer_mode' => 'inline']));
        $ws = Exam::query()->where('kind', 'worksheet')->firstOrFail();
        ExamQuestion::query()->create([
            'exam_id' => $ws->id, 'sort_order' => 0, 'type' => 'multiple_choice', 'body' => 'En küçük asal sayı kaçtır?',
            'options' => ['1', '2', '3', '4'], 'answer' => 'B', 'points' => 0,
        ]);
        // course_id'yi nullable yapan migration yalnızca MySQL'de çalışır; SQLite test veritabanında aynı durumu kur.
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            \Illuminate\Support\Facades\Schema::table('course_homeworks', fn ($t) => $t->unsignedBigInteger('course_id')->nullable()->change());
        }
        $class = \App\Models\SchoolClass::query()->create(['name' => '9', 'section' => 'A', 'grade_level' => 9, 'academic_year' => '2026-2027']);

        $this->actingAs($admin)->get(route('exams.index', ['tab' => 'worksheet']))
            ->assertOk()->assertSee("Ödev olarak ver")->assertSee("9/A")->assertSee("Kademe");

        $this->actingAs($admin)->post(route('exams.assign', $ws), [
            'title' => 'Hafta 1 Çalışma Kâğıdı', 'class_ids' => [$class->id], 'is_public' => '1',
        ])->assertRedirect(route('teacher.assignments.index'));

        $hw = \App\Models\CourseHomework::query()->firstOrFail();
        $this->assertSame('Hafta 1 Çalışma Kâğıdı', $hw->title);
        $this->assertSame($class->id, (int) $hw->school_class_id);
        $this->assertStringEndsWith('.pdf', (string) $hw->attachment_path);
        $this->assertNotEmpty($hw->public_token);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($hw->attachment_path);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($hw->attachment_path);

        $this->actingAs($admin)->post(route('exams.assign', $ws), ['title' => 'x'])->assertSessionHasErrors('class_ids');
    }
}
