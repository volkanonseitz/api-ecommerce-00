<?php

namespace App\Events;

use App\Models\Order;

class OrderCreated
{
    public Order $order;
    public array $invoiceData;
    public $user;

    public function __construct(Order $order, array $invoiceData, $user = null)
    {
        $this->order = $order;
        $this->invoiceData = $invoiceData;
        $this->user = $user;
    }
}