<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Variation;
use App\Modules\Order\Events\OrderProcessed;

class ProductInventoryDecrement
{
    public function handle(OrderProcessed $event): void
    {
        foreach ($event->order->products as $product) {
            $quantity = $product->pivot->order_quantity;
            $product->reserveStock($quantity);
            if ($product->product_type === 'variable' && $product->pivot->variation_option_id) {
                $variation = Variation::find($product->pivot->variation_option_id);
                if ($variation) {
                    $variation->decrement('quantity', $quantity);
                }
            }
        }
    }
}
