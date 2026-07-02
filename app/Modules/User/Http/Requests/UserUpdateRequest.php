<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya pemilik akun sendiri yang boleh lewat request ini.
        // Update user LAIN oleh admin wajib lewat AdminUpdateUserRequest + Policy.
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email:rfc,dns', Rule::unique('users', 'email')->ignore($userId)],
            'profile' => ['nullable', 'array'],
            'profile.id' => ['nullable', 'integer'],
            'profile.bio' => ['nullable', 'string', 'max:1000'],
            'profile.avatar' => ['nullable', 'string', 'max:2048'],
            'address' => ['nullable', 'array'],
            'address.*.id' => ['nullable', 'integer'],
            'address.*.street_address' => ['nullable', 'string', 'max:255'],
            'address.*.city' => ['nullable', 'string', 'max:255'],
            'address.*.state' => ['nullable', 'string', 'max:255'],
            'address.*.zip' => ['nullable', 'string', 'max:20'],
            'address.*.country' => ['nullable', 'string', 'max:255'],
            // shop_id SENGAJA tidak ada di rules -> tidak bisa di-mass-assign oleh customer.
        ];
    }
}
