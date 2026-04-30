<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OpenSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => ['nullable', 'string', 'max:120'],
            'session_date' => ['nullable', 'date', 'after_or_equal:today'],
            'closes_at'    => ['nullable', 'date', 'after:now'],
        ];
    }
}
