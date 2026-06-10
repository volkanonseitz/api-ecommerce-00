<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'exists:shops,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'string'],
            'details' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
        ];
    }
}