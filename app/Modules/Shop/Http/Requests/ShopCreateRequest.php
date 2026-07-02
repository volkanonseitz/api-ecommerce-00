<?php

declare(strict_types=1);

namespace App\Modules\Shop\Http\Requests;

use App\Models\Shop;
use Illuminate\Foundation\Http\FormRequest;

class ShopCreateRequest extends FormRequest
{
    /**
     * SECURITY FIX: authorize() lama `return true` lalu dicek manual di
     * Controller. Sekarang dicek langsung via Policy di titik masuk request
     * (defense in depth) — Request ini tidak bisa "dipinjam" controller lain
     * tanpa otorisasi.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Shop::class) ?? false;
    }

    /**
     * SECURITY FIX (Critical): rules lama berisi `admin_commission_rate`,
     * `total_earnings`, `withdrawn_amount`, `current_balance`, `balance`,
     * dan `is_active` — memungkinkan Store Owner mengatur komisi admin,
     * saldo, dan status aktif toko miliknya sendiri (privilege escalation).
     * Semua field finansial/administratif tersebut DIHAPUS dari sini.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'image' => ['nullable', 'array'],
            'cover_image' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
            'address' => ['nullable', 'array'],
        ];
    }
}
