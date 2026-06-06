<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ManufacturerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'slug' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:10000'],
            'type_id' => ['required', 'exists:types,id'],
            'shop_id' => ['nullable', 'exists:shops,id'],
            'image' => ['nullable', 'array'],
            'cover_image' => ['nullable', 'array'],
            'is_approved' => ['nullable', 'boolean'],
            'language' => ['nullable', 'string'],
            'website' => ['nullable', 'string', 'url'],
            'socials' => ['nullable', 'array'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}