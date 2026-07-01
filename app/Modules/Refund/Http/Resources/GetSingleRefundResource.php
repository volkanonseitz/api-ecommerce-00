<?php

declare(strict_types=1);

namespace App\Modules\Refund\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shop\Http\Resources\ShopResource;
use App\Modules\Order\Http\Resources\OrderResource;
use App\Modules\User\Http\Resources\UserResource;

class GetSingleRefundResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'order_id' => $this->resource->order_id,
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'images' => $this->resource->images,
            'refund_reason_id' => $this->resource->refund_reason_id,
            'customer_id' => $this->resource->customer_id,
            'shop_id' => $this->resource->shop_id,
            'amount' => $this->resource->amount,
            'status' => $this->resource->status,
            'order' => new OrderResource($this->whenLoaded('order')),
            'shop' => new ShopResource($this->whenLoaded('shop')),
            'customer' => new UserResource($this->whenLoaded('customer')),
            'refund_policy' => new RefundPolicyResource($this->whenLoaded('refundPolicy')),
            'refund_reason' => $this->whenLoaded('refundReason'),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}