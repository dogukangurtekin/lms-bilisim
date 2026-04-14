<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required','string','max:120'],
            'last_name' => ['required','string','max:120'],
            'password' => ['nullable','string','min:6','max:72','confirmed'],
            'student_no' => ['required','string','max:50','unique:students,student_no,'.$this->route('student')->id],
            'school_class_id' => ['required','integer','exists:school_classes,id'],
        ];
    }
}
