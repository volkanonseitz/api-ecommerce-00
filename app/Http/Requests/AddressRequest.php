<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'default' => ['nullable', 'boolean'],
            'address' => ['required', 'array'],
            'customer_id' => ['required', 'exists:users,id'],
            'location' => ['nullable', 'array'],
        ];
    }
}