<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        $passwordRules = $id ? ['nullable', 'string', 'min:6'] : ['required', 'string', 'min:6'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($id)],
            'password' => $passwordRules,
            'role' => ['required', 'string', Rule::in(['admin', 'scanner'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
