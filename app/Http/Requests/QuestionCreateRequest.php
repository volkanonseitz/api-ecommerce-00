<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionCreateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'shop_id' => ['required', 'exists:shops,id'],
            'question' => ['required', 'string'],
        ];
    }
}