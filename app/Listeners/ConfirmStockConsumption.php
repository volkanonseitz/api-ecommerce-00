<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Modules\Order\Events\OrderDelivered;

final class ConfirmStockConsumption
{
    public function handle(OrderDelivered $event): void
    {
        foreach ($event->order->products as $product) {
            $quantity = $product->pivot->order_quantity;
            $product->decrement('quantity', $quantity);
            $product->decrement('reserved_quantity', $quantity);
        }
    }
}
