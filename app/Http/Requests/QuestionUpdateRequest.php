<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'answer' => ['required', 'string'],
            'shop_id' => ['nullable', 'exists:shops,id'],
        ];
    }
}