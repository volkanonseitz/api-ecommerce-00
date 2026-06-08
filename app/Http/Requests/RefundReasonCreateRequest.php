<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundReasonCreateRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'slug' => ['nullable', 'string'],
            'language' => ['nullable', 'string'],
        ];
    }
}