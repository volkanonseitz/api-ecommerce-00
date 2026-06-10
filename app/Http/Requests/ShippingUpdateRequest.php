<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShippingUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric'],
            'is_global' => ['nullable', 'boolean'],
            'type' => ['nullable', 'string'],
        ];
    }
}