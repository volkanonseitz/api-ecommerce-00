<?php

declare(strict_types=1);

namespace App\Modules\PaymentIntent\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetPaymentIntentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tracking_number' => ['required', 'string'],
            'payment_gateway' => ['required', 'string'],
            'recall_gateway' => ['nullable', 'boolean'],
        ];
    }
}
