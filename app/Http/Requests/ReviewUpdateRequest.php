<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'photos' => ['nullable', 'array'],
        ];
    }
}