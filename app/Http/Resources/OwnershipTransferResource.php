<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OwnershipTransferResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'transaction_identifier' => $this->resource->transaction_identifier,
            'previous_owner' => new UserResource($this->whenLoaded('previousOwner')),
            'current_owner' => new UserResource($this->whenLoaded('currentOwner')),
            'message' => $this->resource->message,
            'created_by' => $this->resource->created_by,
            'status' => $this->resource->status,
            'shop' => new ShopResource($this->whenLoaded('shop')),
            'order_info' => $this->resource->order_info,
            'balance_info' => $this->resource->balance_info,
            'refund_info' => $this->resource->refund_info,
            'withdrawal_info' => $this->resource->withdrawal_info,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
