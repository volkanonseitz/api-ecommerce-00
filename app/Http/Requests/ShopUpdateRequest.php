<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShopUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:10000'],
            'balance' => ['nullable', 'array'],
            'image' => ['nullable', 'array'],
            'cover_image' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
            'address' => ['nullable', 'array'],
        ];
    }
}