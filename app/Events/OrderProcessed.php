<?php

namespace App\Events;

use App\Models\Order;

class OrderProcessed
{
    public Order $order;
    public function __construct(Order $order) { $this->order = $order; }
}