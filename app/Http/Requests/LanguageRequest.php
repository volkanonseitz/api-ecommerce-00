<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LanguageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'language_name' => ['required', 'string'],
            'language_code' => ['required', 'string'],
            'flag' => ['required', 'string'],
        ];
    }
}