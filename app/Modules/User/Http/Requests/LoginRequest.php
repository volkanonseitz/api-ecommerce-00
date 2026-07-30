<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // 'email' => ['required', 'email:rfc,dns'] //dns akan dinyalakan jika uji coba berhasil nanti,
            'email' => ['required', 'email:rfc'],
            'password' => ['required', 'string'],
        ];
    }
}
