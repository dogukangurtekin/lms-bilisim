<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentHomeworkPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_assignment_detail_uses_inline_file_preview(): void
    {
        $role = Role::query()->create(['name' => 'Student', 'slug' => 'student']);
        $user = User::query()->create(['role_id' => $role->id, 'name' => 'Öğrenci', 'email' => 'prev-student@example.test', 'password' => 'secret123', 'is_active' => true]);

        $this->actingAs($user)->get(route('student.portal.assignments'))
            ->assertOk()
            ->assertSee('id="detail-preview"', false)
            ->assertSee('js/file-preview.js', false)
            ->assertDontSee('detail-image', false);
    }
}
