<?php

namespace App\Modules\Refund\Http\Resources;

use App\Modules\Order\Http\Resources\OrderResource;
use App\Modules\RefundReason\Http\Resources\RefundReasonResource;
use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class GetSingleRefundResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'refund_reason' => RefundReasonResource::make($this->whenLoaded('refundReason')), // Use RefundReasonResource
            'description' => $this->resource->description,
            'amount' => $this->resource->amount,
            'status' => $this->resource->status,
            'images' => $this->resource->images,
            'customer' => UserResource::make($this->whenLoaded('customer')), // Use UserResource
            'order' => OrderResource::make($this->whenLoaded('order')), // Use OrderResource
            'created_at' => $this->resource->created_at,
        ];
    }
}
