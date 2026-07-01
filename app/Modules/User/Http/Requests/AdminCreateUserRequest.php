<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminCreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi berlapis: route sudah dibatasi middleware permission:super_admin,
        // tapi kita cek ulang di Form Request (defense in depth / IDOR-proofing)
        // sehingga FormRequest ini tidak bisa "dipinjam" controller lain tanpa cek ulang.
        return $this->user()?->can('create', \App\Models\User::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'unique:users,email'],
            'password' => [
                'required', 'string', 'min:8', 'max:128',
                'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/',
            ],
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'profile' => ['nullable', 'array'],
            'address' => ['nullable', 'array'],
        ];
    }
}
