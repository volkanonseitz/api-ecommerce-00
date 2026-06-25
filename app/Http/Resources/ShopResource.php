<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    public function toArray($request)
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
            'balance' => $this->resource->whenLoaded('balance', fn () => $this->resource->balance),
            'categories' => $this->resource->whenLoaded('categories', fn () => CategoryResource::collection($this->resource->categories)),
            'orders_count' => $this->resource->whenCounted('orders'),
            'products_count' => $this->resource->whenCounted('products'),
        ];
    }
}
