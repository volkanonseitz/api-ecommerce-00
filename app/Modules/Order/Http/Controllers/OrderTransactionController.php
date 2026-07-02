<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Order;
use App\Modules\Order\DTO\OrderData;
use App\Modules\Order\Http\Requests\CreateOrderRequest;
use App\Modules\Order\Http\Requests\UpdateOrderRequest;
use App\Modules\Order\Http\Resources\OrderResource;
use App\Modules\Order\Services\OrderTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderTransactionController extends BaseController
{
    public function __construct(
        private OrderTransactionService $transactionService
    ) {}

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $this->authorize('create', Order::class);

        $data = OrderData::fromRequest($request->validated());
        $order = $this->transactionService->createOrder($data, $request->user());

        return $this->sendSuccess(
            new OrderResource($order),
            'Order created successfully',
            201
        );
    }

    public function updateStatus(UpdateOrderRequest $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $this->authorize('update', $order);

        $updatedOrder = $this->transactionService->updateOrderStatus(
            $id,
            $request->order_status,
            $request->user()
        );

        return $this->sendSuccess(
            new OrderResource($updatedOrder),
            'Order status updated successfully'
        );
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $order = Order::findOrFail($id);
        $this->authorize('update', $order);

        $reason = $request->get('reason');
        $cancelledOrder = $this->transactionService->cancelOrder(
            $id,
            $request->user(),
            $reason
        );

        return $this->sendSuccess(
            new OrderResource($cancelledOrder),
            'Order cancelled successfully'
        );
    }

    public function updatePaymentStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'payment_status' => 'required|in:paid,unpaid,refunded,pending',
            'payment_note' => 'nullable|string|max:500',
        ]);

        $order = Order::findOrFail($id);
        $this->authorize('update', $order);

        $order->payment_status = $request->get('payment_status');
        $order->payment_note = $request->get('payment_note');
        $order->save();

        return $this->sendSuccess(
            new OrderResource($order->fresh()),
            'Payment status updated successfully'
        );
    }
}
