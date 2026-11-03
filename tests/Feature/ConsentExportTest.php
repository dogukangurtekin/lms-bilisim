<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentCredential;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsentExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        config(['consent.version' => 1]);
        $ar = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $tr = Role::query()->firstOrCreate(['slug' => 'teacher'], ['name' => 'Teacher']);
        $sr = Role::query()->firstOrCreate(['slug' => 'student'], ['name' => 'Student']);
        $gr = Role::query()->firstOrCreate(['slug' => 'guest'], ['name' => 'Misafir']);
        $this->admin = User::query()->create(['role_id' => $ar->id, 'name' => 'Yönetici', 'email' => 'ad@t.l', 'password' => 'secret123', 'is_active' => true]);
        $this->teacher = User::query()->create(['role_id' => $tr->id, 'name' => 'Hoca', 'email' => 'hoca@t.l', 'password' => 'secret123', 'is_active' => true]);
        $t = Teacher::query()->updateOrCreate(['user_id' => $this->teacher->id], []);
        $class = SchoolClass::query()->create(['name' => '5', 'section' => 'A', 'grade_level' => 5, 'teacher_id' => $t->id, 'academic_year' => '2026-2027']);
        foreach (['Onaylı Şükrü' => 1, 'Bekleyen Çağla' => 0] as $name => $v) {
            $u = User::query()->create(['role_id' => $sr->id, 'name' => $name, 'email' => md5($name) . '@t.l', 'password' => 'secret123', 'is_active' => true]);
            User::query()->whereKey($u->id)->update(['consent_version' => $v, 'consent_accepted_at' => $v ? now() : null]);
            $st = Student::query()->updateOrCreate(['user_id' => $u->id], ['student_no' => 'N' . $v, 'school_class_id' => $class->id]);
            StudentCredential::query()->create(['student_id' => $st->id, 'username' => 'k' . $v, 'plain_password' => 'x12345']);
        }
        User::query()->create(['role_id' => $gr->id, 'name' => 'Misafir Kişi', 'email' => 'guest-x@guest.invalid', 'password' => 'x', 'is_active' => true]);
    }

    /** CSV gövdesini satır dizilerine çevirir (BOM atılır, ; ayırıcı); ad => satır. */
    private function rows(string $body): array
    {
        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);
        $lines = array_values(array_filter(explode("\n", $body)));
        $rows = array_map(fn ($l) => str_getcsv($l, ';', '"', '\\'), $lines);
        $header = array_shift($rows);

        return ['header' => $header, 'byName' => collect($rows)->keyBy(0)->all()];
    }

    public function test_admin_downloads_a_csv_with_bom_status_and_class(): void
    {
        $r = $this->actingAs($this->admin)->get(route('users.consent-export'))->assertOk();
        $this->assertStringContainsString('text/csv', (string) $r->headers->get('Content-Type'));
        $this->assertStringContainsString('kvkk-onay-dokumu-', (string) $r->headers->get('Content-Disposition'));
        $body = $r->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $body);

        ['header' => $h, 'byName' => $n] = $this->rows($body);
        $this->assertSame(['Ad Soyad', 'Kullanıcı Adı / E-posta', 'Rol', 'Sınıf', 'Onay Durumu', 'Onay Tarihi', 'Onaylanan Sürüm'], $h);
        $this->assertSame(['k1', 'Öğrenci', '5 A', 'Onayladı'], array_slice($n['Onaylı Şükrü'], 1, 4));
        $this->assertNotSame('', $n['Onaylı Şükrü'][5]);
        $this->assertSame(['k0', 'Öğrenci', '5 A', 'Onaylamadı', ''], array_slice($n['Bekleyen Çağla'], 1, 5));
        $this->assertSame(['hoca@t.l', 'Öğretmen', '', 'Onaylamadı'], array_slice($n['Hoca'], 1, 4));
        $this->assertArrayNotHasKey('Misafir Kişi', $n);
    }

    public function test_filters_apply_to_the_export(): void
    {
        $yes = $this->rows($this->actingAs($this->admin)->get(route('users.consent-export', ['consent' => 'yes']))->streamedContent())['byName'];
        $this->assertSame(['Onaylı Şükrü'], array_keys($yes));

        $no = $this->rows($this->actingAs($this->admin)->get(route('users.consent-export', ['consent' => 'no', 'role' => 'student']))->streamedContent())['byName'];
        $this->assertSame(['Bekleyen Çağla'], array_keys($no));
    }

    public function test_button_is_on_the_users_page_and_teachers_cannot_export(): void
    {
        $this->actingAs($this->admin)->get(route('users.index', ['consent' => 'no']))->assertOk()->assertSee('KVKK Döküm')->assertSee('consent=no', false);
        $this->actingAs($this->teacher)->get(route('users.consent-export'))->assertForbidden();
    }
}
