<?php

namespace App\Http\Requests;

use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'code' => ['required','string','max:30','unique:courses,code'],
            'teacher_id' => ['required','integer','exists:teachers,id'],
            'school_class_id' => ['nullable','integer','exists:school_classes,id'],
            'weekly_hours' => ['required','integer','between:1,20'],
            'lesson_payload' => ['nullable', 'json'],
            'cover_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('teacher_id')) {
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

        if (! $teacherId) {
            $teacherId = Teacher::query()->value('id');
        }

        if ($teacherId) {
            $this->merge(['teacher_id' => $teacherId]);
        }
    }
}
