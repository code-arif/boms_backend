<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isSuperAdmin = $this->user()?->isSuperAdmin();

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'company_id' => [$isSuperAdmin ? 'required' : 'nullable', 'exists:companies,id'],
            'role' => [$isSuperAdmin ? 'required' : 'nullable', 'in:company_admin,employee'],
        ];
    }
}
