<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required','integer','exists:users,id'],
            'student_no' => ['required','string','max:50','unique:students,student_no'],
            'school_class_id' => ['nullable','integer','exists:school_classes,id'],
            'parent_name' => ['nullable','string','max:255'],
            'parent_phone' => ['nullable','string','max:20'],
            'birth_date' => ['nullable','date'],
            'address' => ['nullable','string'],        ];
    }
}
