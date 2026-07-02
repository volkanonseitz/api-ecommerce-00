<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @extends JsonResource<User>
 *
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'email_verified' => (bool) $this->resource->email_verified,
            'is_active' => (bool) $this->resource->is_active,
            // shop_id sengaja hanya ditampilkan jika requester berhak melihatnya,
            // mencegah kebocoran data internal (information disclosure) ke publik.
            'shop_id' => $this->when(
                $request->user()?->can('viewShopAssignment', $this->resource) ?? false,
                $this->resource->shop_id
            ),
            'profile' => $this->whenLoaded('profile'),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
