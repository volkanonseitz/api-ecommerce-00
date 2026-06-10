<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShippingCreateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'amount' => ['nullable', 'numeric'],
            'is_global' => ['nullable', 'boolean'],
            'type' => ['required', 'string'],
        ];
    }
}