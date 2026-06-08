<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutVerifyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric'],
            'customer_id' => ['nullable', 'exists:users,id'],
            'products' => ['required', 'array'],
            'billing_address' => ['nullable', 'array'],
            'shipping_address' => ['nullable', 'array'],
        ];
    }
}