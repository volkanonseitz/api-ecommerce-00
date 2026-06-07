<?php

namespace App\Events;

use App\Models\Order;

class OrderDelivered
{
    public Order $order;
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}