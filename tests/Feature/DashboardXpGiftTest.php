<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\StudentReport;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardXpGiftTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_gift_xp_to_every_active_student_in_a_selected_class(): void
    {
        [$admin, $teacher, $firstClass, $secondClass, $studentRole] = $this->schoolContext();
        $first = $this->student($studentRole, $firstClass, 'Ayşe', 'gift-ayse@example.test', 100, 150);
        $second = $this->student($studentRole, $firstClass, 'Mehmet', 'gift-mehmet@example.test', 200, 100);
        $outside = $this->student($studentRole, $secondClass, 'Zeynep', 'gift-zeynep@example.test', 300, 300);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('XP Hediyesi Gönder')
            ->assertSee('Tüm öğrenciler')
            ->assertSee('Ayşe')
            ->assertSee('data-widget-key="xp_gift" draggable="false"', false)
            ->assertSee('name="submit_xp_gift" value="1"', false);

        $this->actingAs($admin)
            ->post(route('dashboard.xp-gifts.store'), [
                'target_scope' => 'class',
                'class_id' => $firstClass->id,
                'amount' => 250,
                'description' => 'Dönem sonu başarı hediyesi',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('ok');

        $this->assertSame(400, (int) UserProfile::query()->findOrFail($first->id)->xp);
        $this->assertSame(450, (int) UserProfile::query()->findOrFail($second->id)->xp);
        $this->assertSame(300, (int) UserProfile::query()->findOrFail($outside->id)->xp);
        $this->assertSame(400, (int) StudentReport::query()->findOrFail($first->id)->total_xp);
        $this->assertSame(450, (int) StudentReport::query()->findOrFail($second->id)->total_xp);
        $this->assertDatabaseCount('student_xp_grants', 2);
        $this->assertDatabaseHas('student_xp_grants', [
            'student_id' => $first->student->id,
            'amount' => 250,
            'description' => 'Dönem sonu başarı hediyesi',
            'granted_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('dashboard.xp-gifts.store'), [
                'target_scope' => 'students',
                'student_ids' => [$outside->student->id],
                'amount' => 50,
                'description' => 'Bireysel gelişim hediyesi',
            ])
            ->assertRedirect(route('dashboard'));
        $this->assertSame(350, (int) UserProfile::query()->findOrFail($outside->id)->xp);

        $this->actingAs($admin)
            ->post(route('dashboard.xp-gifts.store'), [
                'target_scope' => 'all',
                'amount' => 10,
                'description' => 'Tüm okul hediyesi',
            ])
            ->assertRedirect(route('dashboard'));
        $this->assertSame(410, (int) UserProfile::query()->findOrFail($first->id)->xp);
        $this->assertSame(460, (int) UserProfile::query()->findOrFail($second->id)->xp);
        $this->assertSame(360, (int) UserProfile::query()->findOrFail($outside->id)->xp);
        $this->assertDatabaseCount('student_xp_grants', 6);

        $this->actingAs($teacher)
            ->post(route('dashboard.xp-gifts.store'), [
                'target_scope' => 'all',
                'amount' => 100,
                'description' => 'Yetkisiz gönderim',
            ])
            ->assertForbidden();
    }

    private function schoolContext(): array
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $teacherRole = Role::query()->create(['name' => 'Teacher', 'slug' => 'teacher']);
        $studentRole = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $admin = $this->user($adminRole, 'Admin', 'gift-admin@example.test');
        $teacherUser = $this->user($teacherRole, 'Öğretmen', 'gift-teacher@example.test');
        $teacher = Teacher::query()->create(['user_id' => $teacherUser->id, 'branch' => 'Bilişim']);
        $firstClass = SchoolClass::query()->create(['name' => '7', 'section' => 'A', 'grade_level' => 7, 'teacher_id' => $teacher->id, 'academic_year' => '2026-2027']);
        $secondClass = SchoolClass::query()->create(['name' => '7', 'section' => 'B', 'grade_level' => 7, 'teacher_id' => $teacher->id, 'academic_year' => '2026-2027']);

        return [$admin, $teacherUser, $firstClass, $secondClass, $studentRole];
    }

    private function student(Role $role, SchoolClass $class, string $name, string $email, int $profileXp, int $reportXp): User
    {
        $user = $this->user($role, $name, $email);
        $user->student()->update(['school_class_id' => $class->id]);
        UserProfile::query()->updateOrCreate(['user_id' => $user->id], ['username' => $name, 'role' => 'student', 'xp' => $profileXp]);
        StudentReport::query()->create(['user_id' => $user->id, 'total_xp' => $reportXp]);

        return $user->load('student');
    }

    private function user(Role $role, string $name, string $email): User
    {
        return User::query()->create([
            'role_id' => $role->id,
            'name' => $name,
            'email' => $email,
            'password' => 'secret123',
            'is_active' => true,
        ]);
    }
}
