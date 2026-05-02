<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolClassRequest extends FormRequest
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
            'academic_year' => ['required','string','max:20'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', ''));
        $year = (int) now()->year;
        $month = (int) now()->month;
        $startYear = $month >= 8 ? $year : ($year - 1);
        $gradeLevel = 1;

        if (preg_match('/\d+/', $name, $matches) === 1) {
            $gradeLevel = max(1, min(12, (int) $matches[0]));
        }

        $this->merge([
            'grade_level' => $this->input('grade_level', $gradeLevel),
            'academic_year' => $this->input('academic_year', $startYear . '-' . ($startYear + 1)),
        ]);
    }
}
