<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FlashSaleUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'description' => ['required', 'string', 'max:10000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'slug' => ['nullable', 'string'],
            'language' => ['nullable', 'string'],
            'image' => ['nullable', 'array'],
            'cover_image' => ['nullable', 'array'],
        ];
    }
}