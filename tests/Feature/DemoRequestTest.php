<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SupportRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoRequestTest extends TestCase
{
    use RefreshDatabase;

    private function ok(): array
    {
        return ['guest_name' => 'Ayşe Yılmaz', 'guest_email' => 'ayse@gmail.com', 'school' => 'Atatürk Ortaokulu', 'message' => 'Demo görmek istiyoruz.'];
    }

    public function test_every_field_is_required_and_school_is_saved(): void
    {
        $role = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        User::query()->create(['role_id' => $role->id, 'name' => 'Yönetici', 'email' => 'a@t.l', 'password' => 'secret123', 'is_active' => true]);

        foreach (['guest_name', 'guest_email', 'school', 'message'] as $field) {
            $payload = $this->ok();
            unset($payload[$field]);
            $this->post(route('support-requests.demo'), $payload)->assertSessionHasErrors($field);
        }
        $this->post(route('support-requests.demo'), ['school' => '  '] + $this->ok())->assertSessionHasErrors('school');
        $this->assertSame(0, SupportRequest::query()->count());
    }

    public function test_complete_request_is_saved_with_the_school(): void
    {
        $role = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        User::query()->create(['role_id' => $role->id, 'name' => 'Yönetici', 'email' => 'a2@t.l', 'password' => 'secret123', 'is_active' => true]);

        $this->post(route('support-requests.demo'), $this->ok())->assertSessionHasNoErrors();
        $this->assertStringContainsString('Okul: Atatürk Ortaokulu', SupportRequest::query()->firstOrFail()->message);
    }

    public function test_home_form_has_the_school_field(): void
    {
        $this->get('/')->assertOk()->assertSee('name="school"', false);
    }
}
