<?php

declare(strict_types=1);

namespace App\Modules\Shop\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class RemoveStaffRequest extends FormRequest
{
    /**
     * Kode lama sudah benar secara logika (`! A || ! B` == deny kecuali A dan B
     * dua-duanya true), tapi otorisasi-nya ditulis manual & tersebar di
     * Controller sehingga rawan salah tulis operator jika ada perubahan di
     * masa depan. Dipindahkan ke Policy::manageStaff() terpusat yang sama
     * dipakai endpoint staff lainnya, agar hanya ada satu sumber kebenaran
     * untuk aturan "siapa boleh kelola staff toko X".
     */
    public function authorize(): bool
    {
        /** @var User $staff */
        $staff = $this->route('staff');

        if (! $staff->shop_id) {
            return false;
        }

        $shop = $staff->managed_shop;

        return $shop !== null && ($this->user()?->can('manageStaff', $shop) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
