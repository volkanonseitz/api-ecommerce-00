<?php

namespace App\Actions;

use App\Models\Order;
use App\DTO\OrderData;

class CreateOrderAction
{
    public function execute(OrderData $data): Order
    {
        $attributes = array_filter([
            'tracking_number' => $data->tracking_number,
            'customer_id' => $data->customer_id,
            'shop_id' => $data->shop_id,
            'language' => $data->language,
            'order_status' => $data->order_status,
            'payment_status' => $data->payment_status,
            'amount' => $data->amount,
            'sales_tax' => $data->sales_tax,
            'paid_total' => $data->paid_total,
            'total' => $data->total,
            'delivery_time' => $data->delivery_time,
            'payment_gateway' => $data->payment_gateway,
            'altered_payment_gateway' => $data->altered_payment_gateway,
            'discount' => $data->discount,
            'coupon_id' => $data->coupon_id,
            'logistics_provider' => $data->logistics_provider,
            'billing_address' => $data->billing_address,
            'shipping_address' => $data->shipping_address,
            'delivery_fee' => $data->delivery_fee,
            'customer_contact' => $data->customer_contact,
            'customer_name' => $data->customer_name,
            'note' => $data->note,
            'parent_id' => $data->parent_id,
        ], fn($v) => !is_null($v));

        return Order::create($attributes);
    }
}