<?php

declare(strict_types=1);

namespace App\Modules\Coupon\Http\Resources;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Coupon
 */
class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'language' => $this->language,
            'description' => $this->description,
            'image' => $this->image,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'minimum_cart_amount' => (float) $this->minimum_cart_amount,
            'active_from' => $this->active_from?->toISOString(),
            'expire_at' => $this->expire_at?->toISOString(),
            'is_valid' => $this->is_valid,
            'target' => $this->target,
            'is_approve' => (bool) $this->is_approve,
            'translated_languages' => $this->translated_languages,
            'shop_id' => $this->shop_id,
            'user_id' => $this->user_id,
            'shop' => $this->whenLoaded('shop', fn () => [
                'id' => $this->shop->id,
                'name' => $this->shop->name,
            ]),
        ];
    }
}
