<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OwnershipTransferResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'transaction_identifier' => $this->transaction_identifier,
            'previous_owner' => new UserResource($this->whenLoaded('previousOwner')),
            'current_owner' => new UserResource($this->whenLoaded('currentOwner')),
            'message' => $this->message,
            'created_by' => $this->created_by,
            'status' => $this->status,
            'shop' => new ShopResource($this->whenLoaded('shop')),
            'order_info' => $this->order_info,
            'balance_info' => $this->balance_info,
            'refund_info' => $this->refund_info,
            'withdrawal_info' => $this->withdrawal_info,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}