<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch' => ['nullable','string','max:255'],
            'phone' => ['nullable','string','max:20'],
            'hire_date' => ['nullable','date'],        ];
    }
}
