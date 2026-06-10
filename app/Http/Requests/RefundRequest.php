<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'exists:orders,id'],
            'title' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:10000'],
            'images' => ['nullable', 'array'],
            'refund_reason_id' => ['nullable', 'exists:refund_reasons,id'],
        ];
    }
}