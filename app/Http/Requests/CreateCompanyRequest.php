<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CreateCompanyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:companies,slug', 'regex:/^[a-z0-9-]+$/'],
            'status' => ['nullable', 'in:active,inactive'],
            'plan' => ['nullable', 'in:free,paid'],
            'admin_name' => ['required', 'string', 'max:100'],
            'admin_email' => ['required', 'email', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->slug && $this->name) {
            $this->merge(['slug' => Str::slug($this->name)]);
        }
    }
}
