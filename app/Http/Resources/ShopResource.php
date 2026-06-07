<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'cover_image' => $this->cover_image,
            'logo' => $this->logo,
            'is_active' => $this->is_active,
            'address' => $this->address,
            'settings' => $this->settings,
            'notifications' => $this->notifications,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'balance' => $this->whenLoaded('balance', fn() => $this->balance),
            'categories' => $this->whenLoaded('categories', fn() => CategoryResource::collection($this->categories)),
            'orders_count' => $this->whenCounted('orders'),
            'products_count' => $this->whenCounted('products'),
        ];
    }
}