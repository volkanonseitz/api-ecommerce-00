<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GetSingleRefundResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'refund_reason' => $this->resource->refund_reason->name ?? null,
            'description' => $this->resource->description,
            'amount' => $this->resource->amount,
            'status' => $this->resource->status,
            'images' => $this->resource->images,
            'customer' => [
                'email' => $this->resource->customer->email ?? null,
            ],
            'order' => $this->resource->getOrderData($this->resource->order),
            'created_at' => $this->resource->created_at,
        ];
    }

    private function getOrderData($order)
    {
        if (! $order) {
            return null;
        }

        return [
            'id' => $order->id,
            'tracking_number' => $order->tracking_number,
            'shipping_address' => $order->shipping_address,
            'billing_address' => $order->billing_address,
            'customer_contact' => $order->customer_contact,
            'customer_name' => $order->customer_name,
            'amount' => $order->amount,
            'sales_tax' => $order->sales_tax,
            'discount' => $order->discount,
            'delivery_fee' => $order->delivery_fee,
            'order_status' => $order->order_status,
            'products' => $this->resource->getProductData($order->products),
            'paid_total' => $order->paid_total,
            'created_at' => $order->created_at,
        ];
    }

    private function getProductData($products)
    {
        if (! $products) {
            return [];
        }

        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image,
                'pivot' => [
                    'order_quantity' => $product->pivot->order_quantity ?? null,
                    'subtotal' => $product->pivot->subtotal ?? null,
                ],
            ];
        });
    }
}
