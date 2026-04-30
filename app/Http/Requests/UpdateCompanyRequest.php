<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('company');

        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'slug' => ['sometimes', 'string', 'max:100', "unique:companies,slug,{$id}", 'regex:/^[a-z0-9-]+$/'],
            'status' => ['sometimes', 'in:active,inactive,suspended'],
            'plan' => ['sometimes', 'in:free,paid'],
        ];
    }
}
