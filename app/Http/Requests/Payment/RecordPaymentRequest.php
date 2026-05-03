<?php

namespace App\Http\Requests\Payment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id'  => ['required', 'integer', 'exists:orders,id'],
            'method'    => ['required', 'in:cash,card,mobile'],
            'amount'    => ['nullable', 'numeric', 'min:0.01'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes'     => ['nullable', 'string', 'max:500'],
        ];
    }
}
