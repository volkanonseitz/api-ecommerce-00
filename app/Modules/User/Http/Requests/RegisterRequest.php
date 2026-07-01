<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Requests;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Registrasi publik selalu diizinkan diakses (endpoint publik),
        // pembatasan role dilakukan di controller/action, bukan di sini,
        // karena `permission` yang diminta butuh dicocokkan ke business rule
        // (mis. tidak boleh super_admin) — bukan otorisasi akses endpoint.
        return true;
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
                'required', 'string', 'min:12', 'max:128',
                'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/',
                'regex:/[!@#$%^&*(),.?":{}|<>]/',
                'not_regex:/(\d)\1{2,}/',
                'not_regex:/([A-Za-z])\1{2,}/',
            ],
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
            // Hanya STORE_OWNER yang boleh diminta lewat registrasi publik;
            // validasi nilai final (anti super_admin) tetap dicek ulang di Action.
            'permission' => ['nullable', 'string', 'in:'.Permission::STORE_OWNER->value],
            // shop_id TIDAK ADA di rules ini sama sekali — kalau client kirim,
            // otomatis diabaikan oleh validated() karena bukan bagian dari rules.
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
        ];
    }
}
