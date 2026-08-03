<?php

namespace App\Http\Requests;

use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'code' => ['required','string','max:30','unique:courses,code,'.$this->route('course')->id],
            'teacher_id' => ['nullable','integer','exists:teachers,id'],
            'school_class_id' => ['nullable','integer','exists:school_classes,id'],
            'weekly_hours' => ['required','integer','between:1,20'],
            'parent_course_id' => ['nullable','integer','exists:courses,id'],
            'sort_order' => ['nullable','integer','min:0'],
            'is_active' => ['nullable','boolean'],
            'lesson_payload' => ['nullable', 'string'],
            'cover_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('teacher_id')) {
            return;
        }

        $existingCourse = $this->route('course');
        if ($existingCourse && !empty($existingCourse->teacher_id)) {
            $this->merge(['teacher_id' => $existingCourse->teacher_id]);
            return;
        }

        $user = Auth::user();
        if (! $user?->hasRole('teacher')) {
            return;
        }

        $userId = Auth::id();
        $teacherId = null;

        if ($userId) {
            $teacher = Teacher::firstOrCreate(
                ['user_id' => $userId],
                ['branch' => 'Genel', 'phone' => null, 'hire_date' => now()->toDateString()]
            );
            $teacherId = $teacher->id;
        }

        if ($teacherId) {
            $this->merge(['teacher_id' => $teacherId]);
        }
    }
}
