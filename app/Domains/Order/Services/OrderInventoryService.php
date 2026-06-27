<?php

declare(strict_types=1);

namespace App\Domains\Order\Services;

use App\Enums\ProductType;
use App\Models\Order;
use App\Models\Product;
use App\Models\Variation;
use Illuminate\Support\Facades\DB;

class OrderInventoryService
{
    public function restoreProductInventoryBulk(Order $order): void
    {
        $productIncrements = [];
        $variationIncrements = [];

        // Kumpulkan data ke memori terlebih dahulu untuk optimasi query database
        foreach ($order->products as $product) {
            $quantity = (int) $product->pivot->order_quantity;
            $productIncrements[$product->id] = ($productIncrements[$product->id] ?? 0) + $quantity;

            if ($product->product_type === ProductType::VARIABLE->value && isset($product->pivot->variation_option_id)) {
                $varId = (int) $product->pivot->variation_option_id;
                $variationIncrements[$varId] = ($variationIncrements[$varId] ?? 0) + $quantity;
            }
        }

        // Eksekusi Mass-Update sekaligus dalam satu transaksi database
        DB::transaction(function () use ($productIncrements, $variationIncrements) {
            foreach ($productIncrements as $productId => $qty) {
                Product::where('id', $productId)->increment('quantity', $qty);
            }

            foreach ($variationIncrements as $variationId => $qty) {
                Variation::where('id', $variationId)->increment('quantity', $qty);
            }
        });
    }
}
