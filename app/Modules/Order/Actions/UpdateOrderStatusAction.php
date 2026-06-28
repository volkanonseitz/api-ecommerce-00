<?php

declare(strict_types=1);

namespace App\Modules\Order\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Modules\Order\Services\OrderInventoryService;

class UpdateOrderStatusAction
{
    public function __construct(
        private OrderInventoryService $inventoryService
    ) {}

    public function execute(Order $order, string $newStatus): Order
    {
        $oldStatus = $order->order_status;
        $order->order_status = $newStatus;
        $order->save();

        if ($order->parent_id === null) {
            // Update semua child orders
            $order->children()->update(['order_status' => $newStatus]);
        }

        if ($newStatus === OrderStatus::CANCELLED->value && $oldStatus !== OrderStatus::CANCELLED->value) {
            $this->inventoryService->restoreProductInventoryBulk($order);
        }

        return $order;
    }
}
