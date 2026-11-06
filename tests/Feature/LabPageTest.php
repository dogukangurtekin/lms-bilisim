<?php

namespace Tests\Feature;

use App\Http\Controllers\ActivityController;
use App\Models\GameAssignment;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\User;
use App\Support\LabCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabPageTest extends TestCase
{
    use RefreshDatabase;

    private function mk(string $slug, string $name, string $email): User
    {
        $role = Role::query()->firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);

        return User::query()->create(['role_id' => $role->id, 'name' => $name, 'email' => $email, 'password' => 'secret123', 'is_active' => true]);
    }

    public function test_labs_are_registered_hidden_from_coding_list_and_files_exist(): void
    {
        foreach (LabCatalog::slugs() as $slug) {
            $this->assertArrayHasKey($slug, ActivityController::games());
            $this->assertFileExists(public_path('runner-assets/' . $slug . '/index.html'));
            $this->assertFileExists(public_path('runner-assets/' . $slug . '/app.js'));
            $this->assertFileExists(public_path(ltrim(LabCatalog::LABS[$slug]['icon'], '/')));
        }
        $this->assertFileExists(public_path('runner-assets/lab-shared/lab-core.js'));
        $this->assertFileExists(public_path('runner-assets/lab-shared/lab.css'));

        $admin = $this->mk('admin', 'Yonetici', 'adm@school.local');
        $this->actingAs($admin)->get(route('activities.index'))->assertOk()->assertDontSee('Kural Makinesi');
        $this->actingAs($admin)->get(route('labs.index'))->assertOk()->assertSee('Kural Makinesi')->assertSee('Ödev ver')->assertSee('Yakında')->assertSee('Şifre Kalesi');
    }

    public function test_student_sees_only_assigned_labs_and_staff_can_open_runner(): void
    {
        $teacherUser = $this->mk('teacher', 'Ogretmen', 'ogr@school.local');
        $teacher = Teacher::query()->create(['user_id' => $teacherUser->id, 'branch' => 'Bilisim']);
        $class = SchoolClass::query()->create(['name' => '5', 'section' => 'A', 'grade_level' => 5, 'teacher_id' => $teacher->id, 'academic_year' => '2026-2027']);
        $su = $this->mk('student', 'Ali Veli', 'ali.veli@school.local');
        $su->forceFill(['password_change_required' => false])->save();
        $su->student()->firstOrFail()->update(['school_class_id' => $class->id, 'student_no' => '601']);

        // atama yokken görünmez
        $this->actingAs($su->fresh())->get(route('labs.index'))->assertOk()->assertDontSee('Laboratuvarı aç')->assertSee('açılmış bir laboratuvar yok');

        // ödev verilince görünür ve açılır
        $a = GameAssignment::query()->create(['game_slug' => 'lab-kural-makinesi', 'game_name' => 'Kural Makinesi', 'title' => 'Lab ödevi', 'created_by' => $teacherUser->id]);
        $a->classes()->attach($class->id);
        $this->actingAs($su->fresh())->get(route('labs.index'))->assertOk()->assertSee('Laboratuvarı aç')->assertSee('Kural Makinesi');
        $this->actingAs($su->fresh())->get(route('runner.open', ['slug' => 'lab-kural-makinesi', 'from' => 1, 'to' => 5]))->assertRedirect(url('/lab-kural-makinesi?from=1&to=5'));
        $this->actingAs($su->fresh())->get('/lab-kural-makinesi?from=1&to=5')->assertOk()->assertSee('labApp', false)->assertSee('lab-core.js', false)->assertSee('RUNNER_USER', false);

        // öğretmen doğrudan açabilir
        $this->actingAs($teacherUser)->get('/lab-kural-makinesi?role=teacher')->assertOk()->assertSee('labApp', false);
    }
}
