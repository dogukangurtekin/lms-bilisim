<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_id' => ['nullable','integer','exists:roles,id'],
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email,'.$this->route('user')->id],
            'password' => ['nullable','string','min:8'],
            'is_active' => ['nullable','boolean'],        ];
    }
}
