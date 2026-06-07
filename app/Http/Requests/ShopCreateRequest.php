<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShopCreateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:10000'],
            'admin_commission_rate' => ['nullable', 'numeric'],
            'total_earnings' => ['nullable', 'numeric'],
            'withdrawn_amount' => ['nullable', 'numeric'],
            'current_balance' => ['nullable', 'numeric'],
            'image' => ['nullable', 'array'],
            'cover_image' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
            'address' => ['nullable', 'array'],
            'balance' => ['nullable', 'array'],
        ];
    }
}