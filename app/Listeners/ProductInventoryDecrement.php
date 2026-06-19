<?php

namespace App\Listeners;

use App\Events\OrderProcessed;
use App\Models\Variation;

class ProductInventoryDecrement
{
    public function handle(OrderProcessed $event)
    {
        foreach ($event->order->products as $product) {
            $quantity = $product->pivot->order_quantity;
            $product->decrement('quantity', $quantity);
            if ($product->product_type === 'variable' && $product->pivot->variation_option_id) {
                $variation = Variation::find($product->pivot->variation_option_id);
                if ($variation) {
                    $variation->decrement('quantity', $quantity);
                }
            }
        }
    }
}
