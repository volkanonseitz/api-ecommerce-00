<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tracking_number' => $this->tracking_number,
            'customer_id' => $this->customer_id,
            'shop_id' => $this->shop_id,
            'order_status' => $this->order_status,
            'payment_status' => $this->payment_status,
            'amount' => $this->amount,
            'sales_tax' => $this->sales_tax,
            'paid_total' => $this->paid_total,
            'total' => $this->total,
            'delivery_time' => $this->delivery_time,
            'payment_gateway' => $this->payment_gateway,
            'altered_payment_gateway' => $this->altered_payment_gateway,
            'discount' => $this->discount,
            'coupon_id' => $this->coupon_id,
            'logistics_provider' => $this->logistics_provider,
            'billing_address' => $this->billing_address,
            'shipping_address' => $this->shipping_address,
            'delivery_fee' => $this->delivery_fee,
            'customer_contact' => $this->customer_contact,
            'customer_name' => $this->customer_name,
            'note' => $this->note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'products' => $this->whenLoaded('products', function() {
                return $this->products->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'pivot' => [
                            'order_quantity' => $product->pivot->order_quantity,
                            'unit_price' => $product->pivot->unit_price,
                            'subtotal' => $product->pivot->subtotal,
                            'variation_option_id' => $product->pivot->variation_option_id,
                        ]
                    ];
                });
            }),
            'children' => OrderResource::collection($this->whenLoaded('children')),
            'shop' => $this->whenLoaded('shop', fn() => ['id' => $this->shop->id, 'name' => $this->shop->name]),
            'customer' => $this->whenLoaded('customer', fn() => ['id' => $this->customer->id, 'name' => $this->customer->name]),
            'wallet_point' => $this->whenLoaded('wallet_point'),
            'payment_intent' => $this->whenLoaded('payment_intent'),
        ];
    }
}