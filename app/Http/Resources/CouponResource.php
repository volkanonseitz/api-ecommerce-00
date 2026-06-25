<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'code' => $this->resource->code,
            'language' => $this->resource->language,
            'description' => $this->resource->description,
            'image' => $this->resource->image,
            'type' => $this->resource->type,
            'amount' => $this->resource->amount,
            'minimum_cart_amount' => $this->resource->minimum_cart_amount,
            'active_from' => $this->resource->active_from?->toISOString(),
            'expire_at' => $this->resource->expire_at?->toISOString(),
            'is_valid' => $this->resource->is_valid,
            'target' => $this->resource->target,
            'is_approve' => $this->resource->is_approve,
            'translated_languages' => $this->resource->translated_languages,
            'shop_id' => $this->resource->shop_id,
            'user_id' => $this->resource->user_id,
        ];
    }
}
