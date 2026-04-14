<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required','integer','exists:students,id'],
            'course_id' => ['required','integer','exists:courses,id'],
            'exam_type' => ['required','in:quiz,midterm,final,project'],
            'score' => ['required','numeric','between:0,100'],
            'exam_date' => ['required','date'],
            'term' => ['required','string','max:20'],        ];
    }
}
