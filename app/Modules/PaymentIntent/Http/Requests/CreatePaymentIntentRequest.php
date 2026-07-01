<?php

namespace App\Modules\PaymentIntent\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentIntentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'payment_method_type' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
