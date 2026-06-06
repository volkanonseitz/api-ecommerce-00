<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AttributeValueRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'value' => ['required', 'string', 'max:255'],
            'meta' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric'],
            'shop_id' => ['required', 'exists:shops,id'],
            'attribute_id' => ['required', 'exists:attributes,id'],
            'language' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}