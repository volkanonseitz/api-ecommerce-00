<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentMethodCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method_key' => ['required', 'string'],
            'default_card' => ['nullable', 'boolean'],
            'payment_gateway' => ['required', 'string'],
        ];
    }
}
