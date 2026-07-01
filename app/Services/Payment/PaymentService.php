<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Enums\PaymentGatewayType;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentIntent;
use App\Services\Payment\Factory\PaymentProviderFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentService
{
    /**
     * Get a payment provider instance.
     */
    public function getProvider(string $gateway): \App\Services\Payment\Contracts\PaymentProviderInterface
    {
        return PaymentProviderFactory::create($gateway);
    }

    /**
     * Ambil payment intent berdasarkan tracking number atau order id.
     */
    public function attachPaymentIntent(string $trackingNumber): ?PaymentIntent
    {
        return PaymentIntent::where('tracking_number', $trackingNumber)
            ->orWhere('order_id', $trackingNumber)
            ->first();
    }

    /**
     * Proses pembuatan payment intent.
     */
    public function processPaymentIntent(Request $request, $settings): object
    {
        $data = $request->all();
        $orderTrackingNumber = $data['tracking_number'];
        $requestedGateway = $data['payment_gateway'];
        $order = $this->fetchOrderByTrackingNumber($orderTrackingNumber);
        $initialGateway = $order->payment_gateway;

        // Tentukan gateway yang akan digunakan
        if ($requestedGateway !== $initialGateway) {
            $chosenGateway = ucfirst(strtolower($requestedGateway));
        } else {
            $chosenGateway = $this->getActiveGatewayFromSettings($settings, $requestedGateway);
        }

        if (empty($chosenGateway)) {
            // fallback ke gateway yang sudah ada
            $chosenGateway = ucfirst(strtolower($requestedGateway));
        }

        $exists = $this->paymentIntentExists($orderTrackingNumber, $chosenGateway);
        if (!$exists) {
            $newIntent = $this->savePaymentIntent($order, $chosenGateway, $request);
            if (($data['recall_gateway'] ?? false) && $newIntent) {
                $this->deleteOlderPaymentIntent($orderTrackingNumber, ucfirst(strtolower($order->payment_gateway)));
                $this->updateOrderPaymentGateway($order, $initialGateway, $chosenGateway);
            }

            return $newIntent;
        }

        return PaymentIntent::where(function ($q) use ($orderTrackingNumber) {
            $q->where('tracking_number', $orderTrackingNumber)->orWhere('order_id', $orderTrackingNumber);
        })->where('payment_gateway', $chosenGateway)->firstOrFail();
    }

    protected function getActiveGatewayFromSettings($settings, string $requestedGateway): ?string
    {
        if (isset($settings->options['paymentGateway'])) {
            foreach ($settings->options['paymentGateway'] as $gw) {
                if (strtoupper($gw['name']) === strtoupper($requestedGateway)) {
                    return ucfirst(strtolower($gw['name']));
                }
            }
        }

        return null;
    }

    public function paymentIntentExists(string $trackingNumber, string $gateway): bool
    {
        return PaymentIntent::where(function ($q) use ($trackingNumber) {
            $q->where('tracking_number', $trackingNumber)->orWhere('order_id', $trackingNumber);
        })->where('payment_gateway', $gateway)->exists();
    }

    public function deleteOlderPaymentIntent(string $trackingNumber, string $gateway): void
    {
        PaymentIntent::where(function ($q) use ($trackingNumber) {
            $q->where('tracking_number', $trackingNumber)->orWhere('order_id', $trackingNumber);
        })->where('payment_gateway', $gateway)->forceDelete();
    }

    public function updateOrderPaymentGateway(Order $order, string $oldGateway, string $newGateway): void
    {
        $order->altered_payment_gateway = $oldGateway;
        $order->payment_gateway = strtoupper($newGateway);
        $order->save();

        foreach ($order->children as $child) {
            $child->payment_gateway = strtoupper($newGateway);
            $child->altered_payment_gateway = $oldGateway;
            $child->save();
        }
    }

    public function savePaymentIntent(Order $order, string $gateway, Request $request): PaymentIntent
    {
        $intentInfo = $this->createPaymentIntent($order, $request, $gateway);

        return PaymentIntent::create([
            'order_id' => $order->id,
            'tracking_number' => $order->tracking_number,
            'payment_gateway' => $gateway,
            'payment_intent_info' => $intentInfo,
        ]);
    }

    public function createPaymentIntent(Order $order, Request $request, string $gateway): array
    {
        $provider = $this->getProvider($gateway);

        $data = [
            'amount' => $order->paid_total - intval($order->wallet?->amount),
            'order_tracking_number' => $order->tracking_number,
            'currency' => config('shop.default_currency', 'usd'),
        ];

        if ($request->user()) {
            $data['user_email'] = $order->customer->email;
            $data['name'] = $order->customer->name;
        }

        // Stripe specific: create customer
        if (strtoupper($gateway) === PaymentGatewayType::STRIPE && $request->user()) {
            $customer = $this->createPaymentCustomer($request, $gateway);
            $data['customer'] = $customer['customer_id'];
        }

        // Iyzico specific
        if (strtoupper($gateway) === PaymentGatewayType::IYZICO) {
            $data['ip'] = $request->ip();
        }

        return $provider->createIntent($data);
    }

    public function fetchOrderByTrackingNumber(string $trackingNumber): Order
    {
        $order = Order::where('id', $trackingNumber)->orWhere('tracking_number', $trackingNumber)->first();
        if (!$order) {
            throw new HttpException(404, config('notice.NOT_FOUND'));
        }

        return $order;
    }

    public function createPaymentCustomer(Request $request, string $gateway): array
    {
        $gateway = strtoupper($gateway);
        $user = $request->user();

        $existing = PaymentGateway::where('user_id', $user->id)->where('gateway_name', $gateway)->first();
        if ($existing) {
            return ['customer_id' => $existing->customer_id];
        }

        $provider = $this->getProvider($gateway);
        $customer = $provider->createCustomer([
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
        ]);

        PaymentGateway::create([
            'user_id' => $user->id,
            'customer_id' => $customer['customer_id'],
            'gateway_name' => $gateway,
        ]);

        return $customer;
    }

    public function handleWebhook(string $gateway, Request $request): void
    {
        $provider = $this->getProvider($gateway);
        $provider->handleWebhook($request);
    }
}