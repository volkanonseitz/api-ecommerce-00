<?php

namespace App\Modules\Order\Events;

use App\Models\Order;

class OrderReceived
{
    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
