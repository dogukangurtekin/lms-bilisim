<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveQuizTeacherClassesTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(string $mail): array
    {
        $r = Role::query()->firstOrCreate(['slug' => 'teacher'], ['name' => 'Teacher']);
        $u = User::query()->create(['role_id' => $r->id, 'name' => 'Öğretmen '.$mail, 'email' => $mail, 'password' => 'secret123', 'is_active' => true]);

        return [$u, Teacher::query()->updateOrCreate(['user_id' => $u->id], [])];
    }

    public function test_teacher_sees_and_can_pick_only_assigned_classes(): void
    {
        [$u1, $t1] = $this->teacher('t1@t.l');
        [, $t2] = $this->teacher('t2@t.l');
        $mine = SchoolClass::query()->create(['name' => 'BenimSinif', 'section' => 'A', 'grade_level' => 5, 'teacher_id' => $t1->id, 'academic_year' => '2026-2027']);
        $other = SchoolClass::query()->create(['name' => 'BaskaSinif', 'section' => 'B', 'grade_level' => 6, 'teacher_id' => $t2->id, 'academic_year' => '2026-2027']);

        $this->actingAs($u1)->get(route('live-quiz.index'))->assertOk()->assertSee('BenimSinif')->assertDontSee('BaskaSinif');

        $payload = ['title' => 'Q', 'join_mode' => 'code', 'questions_json' => '[]'];
        $this->actingAs($u1)->post(route('live-quiz.store'), $payload + ['school_class_id' => $other->id])->assertSessionHasErrors('school_class_id');
        $this->actingAs($u1)->post(route('live-quiz.store'), $payload + ['school_class_id' => $mine->id])->assertSessionDoesntHaveErrors('school_class_id');
    }
}
