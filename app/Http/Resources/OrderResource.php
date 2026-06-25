<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'tracking_number' => $this->resource->tracking_number,
            'customer_id' => $this->resource->customer_id,
            'shop_id' => $this->resource->shop_id,
            'order_status' => $this->resource->order_status,
            'payment_status' => $this->resource->payment_status,
            'amount' => $this->resource->amount,
            'sales_tax' => $this->resource->sales_tax,
            'paid_total' => $this->resource->paid_total,
            'total' => $this->resource->total,
            'delivery_time' => $this->resource->delivery_time,
            'payment_gateway' => $this->resource->payment_gateway,
            'altered_payment_gateway' => $this->resource->altered_payment_gateway,
            'discount' => $this->resource->discount,
            'coupon_id' => $this->resource->coupon_id,
            'logistics_provider' => $this->resource->logistics_provider,
            'billing_address' => $this->resource->billing_address,
            'shipping_address' => $this->resource->shipping_address,
            'delivery_fee' => $this->resource->delivery_fee,
            'customer_contact' => $this->resource->customer_contact,
            'customer_name' => $this->resource->customer_name,
            'note' => $this->resource->note,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'products' => $this->resource->whenLoaded('products', function () {
                return $this->resource->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'pivot' => [
                            'order_quantity' => $product->pivot->order_quantity,
                            'unit_price' => $product->pivot->unit_price,
                            'subtotal' => $product->pivot->subtotal,
                            'variation_option_id' => $product->pivot->variation_option_id,
                        ],
                    ];
                });
            }),
            'children' => OrderResource::collection($this->whenLoaded('children')),
            'shop' => $this->resource->whenLoaded('shop', fn () => ['id' => $this->resource->shop->id, 'name' => $this->resource->shop->name]),
            'customer' => $this->resource->whenLoaded('customer', fn () => ['id' => $this->resource->customer->id, 'name' => $this->resource->customer->name]),
            'wallet_point' => $this->resource->whenLoaded('wallet_point'),
            'payment_intent' => $this->resource->whenLoaded('payment_intent'),
        ];
    }
}
