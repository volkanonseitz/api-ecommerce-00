<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SocialLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => [
                'required',
                Rule::in(['google', 'facebook']),
            ],

            'access_token' => [
                'required',
                'string',
                'min:20',
                'max:4096',
            ],
        ];
    }
}