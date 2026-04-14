<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:50'],
            'section' => ['required','string','max:50'],
            'grade_level' => ['required','integer','between:1,12'],
            'teacher_id' => ['nullable','integer','exists:teachers,id'],
            'academic_year' => ['required','string','max:20'],        ];
    }
}
