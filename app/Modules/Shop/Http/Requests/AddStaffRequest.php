<?php

declare(strict_types=1);

namespace App\Modules\Shop\Http\Requests;

use App\Models\Shop;
use Illuminate\Foundation\Http\FormRequest;

class AddStaffRequest extends FormRequest
{
    /**
     * NOTE: kode lama memakai `App\Modules\User\Http\Requests\AdminCreateUserRequest`
     * (otorisasi: `can('create', User::class)`, ability untuk SUPER_ADMIN membuat
     * user apapun) untuk endpoint "Store Owner menambah staff toko sendiri" —
     * dua konteks otorisasi yang berbeda dipaksa pakai satu Request yang sama.
     * Dipisah di sini dengan ability `manageStaff` yang benar (SUPER_ADMIN atau
     * pemilik toko yang bersangkutan).
     */
    public function authorize(): bool
    {
        /** @var Shop $shop */
        $shop = $this->route('shop');

        return $this->user()?->can('manageStaff', $shop) ?? false;
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
        ];
    }
}
