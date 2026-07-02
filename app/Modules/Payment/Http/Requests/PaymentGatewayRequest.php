<?php

declare(strict_types=1);

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentGatewayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'gateway' => ['required', 'string', 'in:stripe,midtrans,xendit'],
        ];
    }
}
