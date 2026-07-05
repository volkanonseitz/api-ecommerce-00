<?php

declare(strict_types=1);

namespace App\Modules\Shop\Http\Resources;

use App\Models\Shop;
use App\Modules\Category\Http\Resources\CategoryResource;
use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Shop
 */
class ShopResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'owner_id' => $this->resource->owner_id,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'cover_image' => $this->resource->cover_image,
            'logo' => $this->resource->logo,
            'is_active' => $this->resource->is_active,
            'address' => $this->resource->address,
            'settings' => $this->resource->settings,
            'notifications' => $this->resource->notifications,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,

            // SECURITY FIX: sebelumnya field ini hanya dilindungi oleh whenLoaded(),
            // artinya kalau ada kode lain yang tidak sengaja eager-load relasi
            // 'balance', data finansial toko langsung bocor ke siapa saja yang
            // bisa lihat toko itu (termasuk publik). Sekarang ditambah pengecekan
            // Policy eksplisit sebagai lapis pertahanan kedua.
            'balance' => $this->when(
                $this->resource->relationLoaded('balance')
                    && $request->user()?->can('viewBalance', $this->resource),
                fn () => $this->resource->balance,
            ),

            'categories' => $this->whenLoaded(
                'categories',
                fn () => CategoryResource::collection($this->resource->categories)
            ),
            'orders_count' => $this->whenCounted('orders'),
            'products_count' => $this->whenCounted('products'),
        ];
    }
}
