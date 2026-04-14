<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required','integer','exists:users,id','unique:teachers,user_id'],
            'branch' => ['nullable','string','max:255'],
            'phone' => ['nullable','string','max:20'],
            'hire_date' => ['nullable','date'],        ];
    }
}
