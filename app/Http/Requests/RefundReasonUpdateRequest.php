<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundReasonUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string'],
        ];
    }
}