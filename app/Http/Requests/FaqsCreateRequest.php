<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FaqsCreateRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'faq_title' => ['required', 'string'],
            'faq_description' => ['required', 'string'],
            'slug' => ['nullable', 'string'],
            'language' => ['nullable', 'string'],
            'shop_id' => ['nullable', 'exists:shops,id'],
        ];
    }
}

// UpdateFaqsRequest (sama)