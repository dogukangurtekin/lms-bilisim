<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required','string','max:255'],
            'content' => ['required','string'],
            'audience' => ['required','in:all,students,teachers'],
            'published_by' => ['required','integer','exists:users,id'],
            'published_at' => ['nullable','date'],        ];
    }
}
