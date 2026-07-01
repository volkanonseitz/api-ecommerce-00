<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'old_password' => ['required', 'string'],
            'new_password' => [
                'required', 'string', 'min:12', 'max:128',
                'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/',
                'regex:/[!@#$%^&*(),.?":{}|<>]/',
                'different:old_password',
                'not_regex:/(\d)\1{2,}/',
                'not_regex:/([A-Za-z])\1{2,}/',
            ],
        ];
    }
}
