<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TypeRequest extends FormRequest
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
            'icon' => ['nullable', 'string'],
            'banners' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
            'promotional_sliders' => ['nullable', 'array'],
            'images' => ['nullable', 'array'],
            'language' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}