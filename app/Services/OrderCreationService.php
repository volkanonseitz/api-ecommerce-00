<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\DTO\OrderData;
use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Enums\PaymentGatewayType;
use App\Enums\PaymentStatus;
use App\Events\OrderProcessed;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentIntent;
use App\Models\Settings;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

class OrderCreationService
{
    public function __construct(
        private OrderPersistenceService $persistence,
        private OrderChildService $childService,
        private OrderProductAttachmentService $attachment,
        private WalletService $wallet,
        private PaymentService $payment,
        private OrderTrackingNumberGenerator $trackingGenerator,
    ) {}

    public function create(OrderData $data, Settings $settings, ?User $user): Order
    {
        return DB::transaction(function () use ($data, $settings, $user) {
            // Set tracking number jika belum
            if (!$data->tracking_number) {
                $data->tracking_number = $this->trackingGenerator->generate();
            }

            // Set customer_id
            if (!$data->customer_id && $user) {
                $data->customer_id = $user->id;
            }

            // Tentukan status awal
            $data->order_status = $this->determineInitialOrderStatus($data->payment_gateway);
            $data->payment_status = $this->determineInitialPaymentStatus($data->payment_gateway);

            // Hitung subtotal jika perlu
            if (!$data->amount && $data->products) {
                $data->amount = $this->calculateSubtotal($data->products);
            }

            // Proses kupon
            $this->applyCoupon($data);

            // Hitung paid_total
            $data->paid_total = $data->amount + $data->sales_tax + $data->delivery_fee - $data->discount;
            $data->total = $data->paid_total;

            // Handle wallet
            $this->handleWallet($data, $user);

            // Buat order induk
            $order = $this->persistence->createParentOrder($data);

            // Attach produk
            $this->attachment->attachProducts($order, $data->products);

            // Buat child orders
            $this->childService->createChildren($order, $data);

            // Deduct wallet points
            $this->deductWalletPoints($order, $data, $user);

            // Buat payment intent jika perlu
            $this->createPaymentIntentIfNeeded($order, $settings);

            event(new OrderProcessed($order));
            return $order;
        });
    }

    private function determineInitialOrderStatus(?string $gateway): string
    {
        return in_array($gateway, [PaymentGatewayType::CASH_ON_DELIVERY, PaymentGatewayType::CASH])
            ? OrderStatus::PROCESSING
            : OrderStatus::PENDING;
    }

    private function determineInitialPaymentStatus(?string $gateway): string
    {
        return match ($gateway) {
            PaymentGatewayType::CASH_ON_DELIVERY => PaymentStatus::CASH_ON_DELIVERY,
            PaymentGatewayType::CASH => PaymentStatus::CASH,
            PaymentGatewayType::FULL_WALLET_PAYMENT => PaymentStatus::SUCCESS,
            default => PaymentStatus::PENDING,
        };
    }

    private function calculateSubtotal(array $products): float
    {
        return array_sum(array_column($products, 'subtotal'));
    }

    private function applyCoupon(OrderData $data): void
    {
        if (!$data->coupon_id) {
            return;
        }

        $coupon = Coupon::find($data->coupon_id);
        if ($coupon && $coupon->type === CouponType::FREE_SHIPPING_COUPON->value) {
            $data->delivery_fee = 0;
        }
        if ($coupon) {
            $data->discount = $coupon->type === 'percentage'
                ? ($coupon->amount / 100) * $data->amount
                : min($coupon->amount, $data->amount);
        }
    }

    private function handleWallet(OrderData $data, ?User $user): void
    {
        if (!$data->use_wallet_points || !$user || !$user->wallet) {
            return;
        }

        $wallet = $user->wallet;
        $walletCurrency = $this->wallet->walletPointsToCurrency($wallet->available_points);
        $amountDue = $data->paid_total - $walletCurrency;

        if ($amountDue <= 0) {
            $data->payment_gateway = PaymentGatewayType::FULL_WALLET_PAYMENT;
            $data->order_status = OrderStatus::COMPLETED;
            $data->payment_status = PaymentStatus::SUCCESS;
            $data->paid_total = $data->total; // reset
        } else {
            $data->paid_total = $amountDue;
        }
    }

    private function deductWalletPoints(Order $order, OrderData $data, ?User $user): void
    {
        if (!$data->use_wallet_points || !$user || !$user->wallet) {
            return;
        }

        $pointsUsed = $this->wallet->currencyToWalletPoints($data->paid_total);
        if ($pointsUsed > 0) {
            $this->wallet->deductPoints($user->id, $pointsUsed);
            \App\Models\OrderWalletPoint::create(['amount' => $pointsUsed, 'order_id' => $order->id]);
        }
    }

    private function createPaymentIntentIfNeeded(Order $order, Settings $settings): void
    {
        if (in_array($order->payment_gateway, [
            PaymentGatewayType::CASH,
            PaymentGatewayType::CASH_ON_DELIVERY,
            PaymentGatewayType::FULL_WALLET_PAYMENT,
        ])) {
            return;
        }

        $intent = $this->payment->createPaymentIntent($order, $settings, $order->payment_gateway);
        PaymentIntent::create([
            'order_id' => $order->id,
            'tracking_number' => $order->tracking_number,
            'payment_gateway' => ucfirst($order->payment_gateway),
            'payment_intent_info' => $intent,
        ]);
    }
}