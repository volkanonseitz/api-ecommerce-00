<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RefundResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'refund_reason' => ['name' => $this->resource->refund_reason->name ?? null],
            'amount' => $this->resource->amount,
            'status' => $this->resource->status,
            'customer' => ['email' => $this->resource->customer->email ?? null],
            'order' => [
                'id' => $this->resource->order->id ?? null,
                'tracking_number' => $this->resource->order->tracking_number ?? null,
                'created_at' => $this->resource->created_at,
            ],
            'created_at' => $this->resource->created_at,
        ];
    }
}
