<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:120'],
            'description'  => ['nullable', 'string', 'max:500'],
            'price'        => ['required', 'numeric', 'min:0'],
            'category'     => ['nullable', 'string', 'max:60'],
            'is_available' => ['nullable', 'boolean'],
        ];
    }
}
