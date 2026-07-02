<?php

namespace App\Modules\Withdraw\Http\Resources;

use App\Models\Withdraw;
use App\Modules\Shop\Http\Resources\ShopResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Withdraw
 */
class WithdrawResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'shop_id' => $this->resource->shop_id,
            'amount' => $this->resource->amount,
            'payment_method' => $this->resource->payment_method,
            'details' => $this->resource->details,
            'note' => $this->resource->note,
            'status' => $this->resource->status,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'deleted_at' => $this->resource->deleted_at,
            'shop' => new ShopResource($this->whenLoaded('shop')),
        ];
    }
}
