<?php

declare(strict_types=1);

namespace App\Modules\Author\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Otorisasi di controller via Policy
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'slug' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            'shop_id' => ['nullable', 'exists:shops,id'],
            'image' => ['nullable', 'array'],
            'cover_image' => ['nullable', 'array'],
            'is_approved' => ['nullable', 'boolean'],
            'language' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
