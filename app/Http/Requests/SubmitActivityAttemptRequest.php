<?php

namespace App\Http\Requests;

use App\Models\CodingActivity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SubmitActivityAttemptRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'answers' => ['required','array'],
            'answers.*' => ['nullable'],
            'duration_seconds' => ['nullable','integer','min:0','max:7200'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $activity = $this->route('activity');
            if (! $activity instanceof CodingActivity) {
                return;
            }

            $activity->loadMissing('questions');

            $answers = (array) $this->input('answers', []);
            foreach ($activity->questions as $question) {
                $given = $answers[$question->id] ?? null;
                $hasAnswer = is_array($given)
                    ? count(array_filter($given, fn ($v) => trim((string) $v) !== '')) > 0
                    : trim((string) $given) !== '';
                if (! $hasAnswer) {
                    $validator->errors()->add('answers', 'Soruları işaretleyin.');
                    return;
                }
            }
        });
    }
}