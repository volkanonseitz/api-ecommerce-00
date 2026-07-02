<?php

namespace App\Modules\Wishlist\Http\Resources;

use App\Models\Wishlist;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Wishlist
 */
class WishlistResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'product_id' => $this->resource->product_id,
            'user_id' => $this->resource->user_id,
            'variation_option_id' => $this->resource->variation_option_id,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
