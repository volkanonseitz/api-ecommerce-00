<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentIntent;
use Illuminate\Http\Request;

class PaymentService
{
    public function attachPaymentIntent(string $orderTrackingNumber)
    {
        return PaymentIntent::where('tracking_number', $orderTrackingNumber)
            ->orWhere('order_id', $orderTrackingNumber)
            ->first();
    }

    public function processPaymentIntent(Order $order, Request $request, array $settings): ?PaymentIntent
    {
        $chosenGateway = $this->determinePaymentGateway($order, $request, $settings);
        if (! $chosenGateway) {
            return null;
        }

        $existing = PaymentIntent::where(function ($q) use ($order) {
            $q->where('tracking_number', $order->tracking_number)
                ->orWhere('order_id', $order->id);
        })->where('payment_gateway', $chosenGateway)->first();

        if ($existing) {
            return $existing;
        }

        // Create new payment intent (call external API via facade)
        $intentData = $this->createPaymentIntentData($order, $request, $chosenGateway);

        // Simpan ke database
        return PaymentIntent::create([
            'order_id' => $order->id,
            'tracking_number' => $order->tracking_number,
            'payment_gateway' => $chosenGateway,
            'payment_intent_info' => $intentData,
        ]);
    }

    private function determinePaymentGateway(Order $order, Request $request, array $settings): ?string
    {
        $requested = $request->payment_gateway ?? $order->payment_gateway;
        if ($requested !== $order->payment_gateway) {
            return ucfirst(strtolower($requested));
        }
        // cari dari settings
        foreach ($settings['paymentGateway'] ?? [] as $gw) {
            if (strtoupper($gw['name']) === $requested) {
                return ucfirst($gw['name']);
            }
        }

        return null;
    }

    private function createPaymentIntentData(Order $order, Request $request, string $gateway): array
    {
        // Panggil Payment facade atau gateway langsung
        // Contoh sederhana
        return [
            'amount' => $order->paid_total,
            'order_tracking_number' => $order->tracking_number,
            'gateway' => $gateway,
        ];
    }

    public function webhookSuccessResponse(Order $order, string $orderStatus, string $paymentStatus): void
    {
        if ($this->isOrderFinal($order)) {
            return;
        }
        $order->order_status = $orderStatus;
        $order->payment_status = $paymentStatus;
        $order->save();

        foreach ($order->children as $child) {
            $child->order_status = $orderStatus;
            $child->payment_status = $paymentStatus;
            $child->save();
        }
        // Trigger status management jika perlu
        $this->orderStatusManagementOnPayment($order, OrderStatus::PROCESSING, $paymentStatus);
    }

    private function isOrderFinal(Order $order): bool
    {
        return in_array($order->order_status, [OrderStatus::COMPLETED->value, OrderStatus::CANCELLED->value]);
    }

    private function orderStatusManagementOnPayment(Order $order, string $newStatus, string $paymentStatus): void
    {
        // Implementasi sesuai kebutuhan
    }
}
