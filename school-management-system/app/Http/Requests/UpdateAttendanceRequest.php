<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required','integer','exists:students,id'],
            'course_id' => ['nullable','integer','exists:courses,id'],
            'date' => ['required','date'],
            'status' => ['required','in:present,absent,late,excused'],
            'note' => ['nullable','string','max:255'],        ];
    }
}
