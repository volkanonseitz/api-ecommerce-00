<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Events\OrderDelivered;
use App\Models\Order;
use App\Models\Refund;
use App\Models\User;
use App\Modules\Order\Actions\CreateOrderAction;
use App\Modules\Order\Actions\UpdateOrderStatusAction;
use App\Modules\Order\DTO\OrderData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderTransactionService
{
    public function __construct(
        private CreateOrderAction $createOrder,
        private UpdateOrderStatusAction $updateOrderStatus,
        private OrderCacheService $cacheService,
        private OrderInventoryService $inventoryService
    ) {}

    public function createOrder(OrderData $data, User $user): Order
    {
        return DB::transaction(function () use ($data, $user) {
            try {
                // Validate inventory availability
                $this->inventoryService->validateInventoryAvailability($data);

                // Create order
                $order = $this->createOrder->execute($data, $user);

                // Decrement inventory
                $this->inventoryService->decrementInventory($order);

                // Invalidate cache
                $this->cacheService->invalidateAllOrderCache();

                // Log order creation
                Log::info('Order created', [
                    'order_id' => $order->id,
                    'tracking_number' => $order->tracking_number,
                    'user_id' => $user->id,
                    'total' => $order->total,
                    'action' => 'create',
                ]);

                return $order;
            } catch (\Exception $e) {
                Log::error('Order creation failed', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'data' => $data->toArray(),
                ]);
                throw $e;
            }
        });
    }

    public function updateOrderStatus(int $orderId, string $status, User $user): Order
    {
        $order = Order::findOrFail($orderId);

        return DB::transaction(function () use ($order, $status, $user) {
            try {
                // Update status
                $updatedOrder = $this->updateOrderStatus->execute($order, $status);

                // Handle status-specific logic
                $this->handleStatusChange($updatedOrder, $status, $user);

                // Invalidate cache
                $this->cacheService->invalidateOrderCache($order->id, $user->id);

                // Log status update
                Log::info('Order status updated', [
                    'order_id' => $order->id,
                    'old_status' => $order->order_status,
                    'new_status' => $status,
                    'user_id' => $user->id,
                    'action' => 'update_status',
                ]);

                return $updatedOrder;
            } catch (\Exception $e) {
                Log::error('Order status update failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                ]);
                throw $e;
            }
        });
    }

    public function cancelOrder(int $orderId, User $user, ?string $reason = null): Order
    {
        $order = Order::findOrFail($orderId);

        return DB::transaction(function () use ($order, $user, $reason) {
            try {
                // Cancel order
                $order->order_status = 'order-cancelled';
                $order->cancelled_at = now();
                if ($reason) {
                    $order->note = $reason;
                }
                $order->save();

                // Restore inventory
                $this->inventoryService->restoreInventory($order);

                // Handle refund if payment was made
                $this->handleRefund($order, $user);

                // Invalidate cache
                $this->cacheService->invalidateOrderCache($order->id, $user->id);

                // Log cancellation
                Log::info('Order cancelled', [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'reason' => $reason,
                    'action' => 'cancel',
                ]);

                return $order->fresh();
            } catch (\Exception $e) {
                Log::error('Order cancellation failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                ]);
                throw $e;
            }
        });
    }

    private function handleStatusChange(Order $order, string $status, User $user): void
    {
        switch ($status) {
            case 'order-completed':
                $this->handleOrderCompleted($order, $user);
                break;
            case 'order-cancelled':
                $this->cancelOrder($order->id, $user);
                break;
            case 'order-refunded':
                $this->handleRefund($order, $user);
                break;
        }
    }

    private function handleOrderCompleted(Order $order, User $user): void
    {
        // Commission calculation logic
        if ($order->commission_rate > 0) {
            $commission = $order->total * ($order->commission_rate / 100);
            $order->admin_revenue = $commission;
            $order->shop_revenue = $order->total - $commission;
            $order->save();
        }

        // Emit event for completed order
        event(new OrderDelivered($order));
    }

    private function handleRefund(Order $order, User $user): void
    {
        // Create refund record
        if ($order->payment_status === 'paid') {
            Refund::create([
                'order_id' => $order->id,
                'amount' => $order->total,
                'reason' => 'Order cancelled',
                'status' => 'pending',
            ]);

            $order->payment_status = 'refunded';
            $order->save();
        }
    }
}
