<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'details' => $this->details,
            'note' => $this->note,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'shop' => new ShopResource($this->whenLoaded('shop')),
        ];
    }
}