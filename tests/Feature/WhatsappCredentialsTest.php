<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentCredential;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WhatsappCredentialsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $teacher;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $ar = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $tr = Role::query()->firstOrCreate(['slug' => 'teacher'], ['name' => 'Teacher']);
        $sr = Role::query()->firstOrCreate(['slug' => 'student'], ['name' => 'Student']);
        $this->admin = User::query()->create(['role_id' => $ar->id, 'name' => 'Yönetici', 'email' => 'ad@t.l', 'password' => 'secret123', 'is_active' => true]);
        $this->teacher = User::query()->create(['role_id' => $tr->id, 'name' => 'Hoca Hanım', 'email' => 'hoca@okul.com', 'password' => 'oldpass123', 'is_active' => true]);
        $teacher = Teacher::query()->updateOrCreate(['user_id' => $this->teacher->id], ['phone' => '0532 111 22 33']);
        $class = SchoolClass::query()->create(['name' => '5-A', 'section' => 'A', 'grade_level' => 5, 'teacher_id' => $teacher->id, 'academic_year' => '2026-2027']);
        $this->student = User::query()->create(['role_id' => $sr->id, 'name' => 'Ali Veli', 'email' => 'ali123@okul.local', 'password' => 'secret123', 'is_active' => true]);
        $st = Student::query()->updateOrCreate(['user_id' => $this->student->id], ['student_no' => 'N1', 'school_class_id' => $class->id, 'parent_phone' => '0555 444 33 22']);
        StudentCredential::query()->create(['student_id' => $st->id, 'username' => 'ali123', 'plain_password' => 'Sifre4567']);
    }

    public function test_student_row_has_a_whatsapp_link_with_login_page_username_and_password(): void
    {
        $html = $this->actingAs($this->admin)->get(route('users.index'))->assertOk()->getContent();
        $this->assertStringContainsString('📲 WhatsApp', $html);
        $this->assertSame(1, preg_match('#https://wa\.me/\?text=([^"\']+)#', $html, $m));
        $text = urldecode(html_entity_decode($m[1]));
        $this->assertStringContainsString(route('login'), $text);
        $this->assertStringContainsString('Kullanıcı adı: ali123', $text);
        $this->assertStringContainsString('Şifre: Sifre4567', $text);
        $this->assertStringContainsString('Değerli velim, Ali Veli adlı öğrencimizin', $text);
        $this->assertStringContainsString('Bilişim Teknolojileri Öğrt.', $text);
    }

    public function test_student_without_credential_gets_a_disabled_button(): void
    {
        StudentCredential::query()->delete();
        $this->actingAs($this->admin)->get(route('users.index'))->assertSee('Bu öğrencinin kayıtlı giriş bilgisi yok');
    }

    public function test_teacher_button_resets_password_and_redirects_to_whatsapp_with_the_new_one(): void
    {
        $r = $this->actingAs($this->admin)->post(route('users.whatsapp-credentials', $this->teacher));
        $loc = (string) $r->headers->get('Location');
        $this->assertStringStartsWith('https://wa.me/?text=', $loc);

        $text = urldecode(substr($loc, strlen('https://wa.me/?text=')));
        $this->assertStringContainsString('Sayın Hoca Hanım', $text);
        $this->assertStringContainsString(route('login'), $text);
        $this->assertStringContainsString('Kullanıcı adı: hoca@okul.com', $text);
        preg_match('/Şifre: (\S+)/u', $text, $m);
        $this->assertTrue(Hash::check($m[1], $this->teacher->fresh()->password));
        $this->assertFalse(Hash::check('oldpass123', $this->teacher->fresh()->password));
    }

    public function test_only_admin_and_only_teachers(): void
    {
        $this->actingAs($this->teacher)->post(route('users.whatsapp-credentials', $this->teacher))->assertForbidden();
        $this->actingAs($this->admin)->post(route('users.whatsapp-credentials', $this->student))->assertNotFound();
        $this->actingAs($this->admin)->post(route('users.whatsapp-credentials', $this->admin))->assertNotFound();
    }
}
