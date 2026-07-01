<?php

declare(strict_types=1);

namespace App\Modules\Shop\Http\Requests;

use App\Models\Shop;
use Illuminate\Foundation\Http\FormRequest;

class ShopUpdateRequest extends FormRequest
{
    /**
     * SECURITY FIX (IDOR): otorisasi dicek terhadap instance Shop spesifik
     * yang diambil dari route model binding (`{shop}`), bukan cuma `return true`.
     * Ini mencegah user A mengubah toko milik user B hanya dengan mengganti
     * ID di URL — Laravel akan 403 sebelum method controller dijalankan sama sekali.
     */
    public function authorize(): bool
    {
        /** @var Shop $shop */
        $shop = $this->route('shop');

        return $this->user()?->can('update', $shop) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
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
