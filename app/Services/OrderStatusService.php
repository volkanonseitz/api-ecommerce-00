<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Enums\Permission;
use App\Models\Order;
use App\Traits\AuthorizesShopAccess;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class OrderStatusService
{
    use AuthorizesShopAccess;

    public function updateStatus(Order $order, string $newStatus, Authenticatable $user): Order
    {
        $this->authorizeShop($user, $order->shop_id, 'update');

        $oldStatus = $order->order_status;
        $order->order_status = $newStatus;
        $order->save();

        // Update child orders jika parent
        if ($order->parent_id === null) {
            foreach ($order->children as $child) {
                $child->order_status = $newStatus;
                $child->save();
            }
        }

        // Handle inventory jika batal
        if ($newStatus === OrderStatus::CANCELLED && $oldStatus !== OrderStatus::CANCELLED) {
            $this->restoreInventory($order);
        }

        return $order;
    }

    private function restoreInventory(Order $order): void
    {
        foreach ($order->products as $product) {
            $quantity = $product->pivot->order_quantity;
            $product->increment('quantity', $quantity);
            if ($product->product_type === 'variable' && $product->pivot->variation_option_id) {
                $variation = \App\Models\Variation::find($product->pivot->variation_option_id);
                if ($variation) {
                    $variation->increment('quantity', $quantity);
                }
            }
        }
    }
}