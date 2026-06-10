<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RefundResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'refund_reason' => ['name' => $this->refund_reason->name ?? null],
            'amount' => $this->amount,
            'status' => $this->status,
            'customer' => ['email' => $this->customer->email ?? null],
            'order' => [
                'id' => $this->order->id ?? null,
                'tracking_number' => $this->order->tracking_number ?? null,
                'created_at' => $this->created_at,
            ],
            'created_at' => $this->created_at,
        ];
    }
}