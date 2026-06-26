<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/'],
            'shop_id' => ['prohibited'], // Must never be set by client
            'profile' => ['nullable', 'array'],
            'profile.avatar' => ['nullable', 'string', 'max:2048'],
            'profile.bio' => ['nullable', 'string', 'max:1000'],
            'profile.socials' => ['nullable', 'array'],
            'address' => ['nullable', 'array'],
            'address.street_address' => ['required_with:address', 'string', 'max:255'],
            'address.city' => ['required_with:address', 'string', 'max:255'],
            'address.state' => ['required_with:address', 'string', 'max:255'],
            'address.zip' => ['required_with:address', 'string', 'max:20'],
            'address.country' => ['required_with:address', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
            'shop_id.prohibited' => 'Shop assignment is not allowed.',
        ];
    }
}
