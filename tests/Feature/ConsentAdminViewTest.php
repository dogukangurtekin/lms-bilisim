<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsentAdminViewTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        config(['consent.version' => 1]);
        $ar = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $tr = Role::query()->firstOrCreate(['slug' => 'teacher'], ['name' => 'Teacher']);
        $sr = Role::query()->firstOrCreate(['slug' => 'student'], ['name' => 'Student']);
        $this->admin = User::query()->create(['role_id' => $ar->id, 'name' => 'Yönetici', 'email' => 'ad@t.l', 'password' => 'secret123', 'is_active' => true]);
        $tu = User::query()->create(['role_id' => $tr->id, 'name' => 'Öğretmen', 'email' => 'te@t.l', 'password' => 'secret123', 'is_active' => true]);
        $teacher = Teacher::query()->updateOrCreate(['user_id' => $tu->id], []);
        $class = SchoolClass::query()->create(['name' => '5-A', 'section' => 'A', 'grade_level' => 5, 'teacher_id' => $teacher->id, 'academic_year' => '2026-2027']);
        foreach (['Onaylı Ali' => 1, 'Bekleyen Veli' => 0] as $name => $v) {
            $u = User::query()->create(['role_id' => $sr->id, 'name' => $name, 'email' => str_replace(' ', '', $name) . '@t.l', 'password' => 'secret123', 'is_active' => true]);
            User::query()->whereKey($u->id)->update(['consent_version' => $v, 'consent_accepted_at' => $v ? now() : null]);
            Student::query()->updateOrCreate(['user_id' => $u->id], ['student_no' => 'N' . $v, 'school_class_id' => $class->id]);
        }
    }

    public function test_admin_sees_consent_badges_counters_and_can_filter_students(): void
    {
        $r = $this->actingAs($this->admin)->get(route('students.index'))->assertOk();
        $r->assertSee('Onaylı Ali')->assertSee('✅ Onayladı')->assertSee('⏳ Onaylamadı')->assertSee('1</span> / 2 öğrenci onayladı', false);

        $this->actingAs($this->admin)->get(route('students.index', ['consent' => 'yes']))->assertSee('Onaylı Ali')->assertDontSee('Bekleyen Veli');
        $this->actingAs($this->admin)->get(route('students.index', ['consent' => 'no']))->assertSee('Bekleyen Veli')->assertDontSee('Onaylı Ali');
    }

    public function test_users_list_shows_status_and_filters(): void
    {
        $this->actingAs($this->admin)->get(route('users.index'))->assertOk()->assertSee('Onayladı')->assertSee('Onaylamadı');
        $this->actingAs($this->admin)->get(route('users.index', ['consent' => 'yes']))->assertSee('Onaylı Ali')->assertDontSee('Bekleyen Veli');
        $this->actingAs($this->admin)->get(route('users.index', ['consent' => 'no']))->assertSee('Bekleyen Veli')->assertDontSee('Onaylı Ali');
    }

    public function test_teacher_does_not_see_consent_info_on_student_list(): void
    {
        $teacher = User::query()->where('email', 'te@t.l')->firstOrFail();
        $this->actingAs($teacher)->get(route('students.index'))->assertOk()->assertDontSee('Onaylamadı')->assertDontSee('öğrenci onayladı');
    }
}
