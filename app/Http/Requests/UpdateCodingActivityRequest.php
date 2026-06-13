<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCodingActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()?->role?->slug, ['admin','teacher'], true);
    }

    public function rules(): array
    {
        return [
            'title' => ['required','string','max:255'],
            'type' => ['required','in:daily_task,quiz,race,live_quiz'],
            'instruction' => ['nullable','string'],
            'lesson_pages' => ['nullable','array'],
            'lesson_pages.*' => ['nullable','string','max:1500'],
            'base_xp' => ['nullable','integer','min:0','max:500'],
            'is_random_pool' => ['nullable','boolean'],
            'questions' => ['nullable','array'],
            'questions.*.prompt' => ['required_with:questions','string','max:1000'],
            'questions.*.question_type' => ['required_with:questions','in:single_choice,multi_choice,short_text,code_output'],
            'questions.*.points' => ['nullable','integer','min:1','max:100'],
            'questions.*.answer' => ['nullable','string','max:255'],
            'questions.*.options' => ['nullable','array'],
            'questions.*.options.*' => ['nullable','string','max:255'],
            'questions.*.correct_options' => ['nullable','array'],
        ];
    }
}
