<?php

declare(strict_types=1);

namespace App\Services\Payment\Providers;

use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\SetupIntent;
use Stripe\Stripe;

class StripeProvider extends AbstractPaymentProvider
{
    public function __construct()
    {
        $this->gatewayName = 'stripe';
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPayment(array $data): array
    {
        $params = [
            'amount' => $data['amount'] * 100,
            'currency' => $data['currency'] ?? config('shop.default_currency', 'usd'),
            'metadata' => ['order_tracking_number' => $data['order_tracking_number'] ?? null],
        ];

        if (!empty($data['customer_id'])) {
            $params['customer'] = $data['customer_id'];
        }

        if (!empty($data['payment_method_id'])) {
            $params['payment_method'] = $data['payment_method_id'];
        }

        // Handle payment method type specific options
        if (!empty($data['payment_method_options'])) {
            $params['payment_method_options'] = $data['payment_method_options'];
        }

        // Handle billing and shipping addresses
        if (!empty($data['billing_address'])) {
            $params['billing_details'] = $data['billing_address'];
        }

        if (!empty($data['shipping_address'])) {
            $params['shipping'] = $data['shipping_address'];
        }

        $intent = PaymentIntent::create($params);

        $response = [
            'id' => $intent->id,
            'status' => $intent->status,
            'client_secret' => $intent->client_secret,
        ];

        // For Stripe, payment method type is determined from payment method
        if (!empty($data['payment_method_id'])) {
            $paymentMethod = StripePaymentMethod::retrieve($data['payment_method_id']);
            $response['payment_method_type'] = $paymentMethod->type;
        }

        return $response;
    }

    public function createCustomer(array $data): array
    {
        $customerData = [
            'email' => $data['email'],
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
        ];
        
        if (isset($data['user_id'])) {
            $customerData['metadata'] = ['user_id' => (string) $data['user_id']];
        }

        if (isset($data['metadata'])) {
            $customerData['metadata'] = array_merge(
                $customerData['metadata'] ?? [],
                $data['metadata']
            );
        }

        if (isset($data['mobile_number'])) {
            $customerData['phone'] = $data['mobile_number'];
        }

        if (isset($data['addresses'])) {
            $customerData['address'] = $data['addresses'][0] ?? null;
        }

        $customer = Customer::create($customerData);

        return [
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'name' => $customer->name,
            'metadata' => $customer->metadata?->toArray() ?? []
        ];
    }

    public function handleWebhook(object $request): void
    {
        $payload = $request->all();
        $eventType = $payload['type'] ?? null;

        switch ($eventType) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSuccess($payload['data']['object'] ?? []);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($payload['data']['object'] ?? []);
                break;
            case 'payment_intent.created':
                $this->handlePaymentCreated($payload['data']['object'] ?? []);
                break;
            default:
                \Log::info('Stripe webhook received: ' . $eventType);
        }
    }

    protected function handlePaymentSuccess(array $data): void
    {
        $orderTrackingNumber = $data['metadata']['order_tracking_number'] ?? null;
        if ($orderTrackingNumber) {
            \Log::info('Stripe payment success for order: ' . $orderTrackingNumber);
            // Update order status logic here
        }
    }

    protected function handlePaymentFailed(array $data): void
    {
        $orderTrackingNumber = $data['metadata']['order_tracking_number'] ?? null;
        if ($orderTrackingNumber) {
            \Log::warning('Stripe payment failed for order: ' . $orderTrackingNumber);
        }
    }

    protected function handlePaymentCreated(array $data): void
    {
        $orderTrackingNumber = $data['metadata']['order_tracking_number'] ?? null;
        if ($orderTrackingNumber) {
            \Log::info('Stripe payment created for order: ' . $orderTrackingNumber);
        }
    }

    public function retrievePaymentMethod(string $methodKey, ?string $type = null): object
    {
        return StripePaymentMethod::retrieve($methodKey);
    }

    public function attachPaymentMethodToCustomer(string $methodKey, object $user, ?string $type = null): object
    {
        $paymentMethod = StripePaymentMethod::retrieve($methodKey);
        return $paymentMethod;
    }

    public function detachPaymentMethod(string $methodKey, ?string $type = null): void
    {
        $paymentMethod = StripePaymentMethod::retrieve($methodKey);
        $paymentMethod->detach();
    }

    public function savePaymentMethod(object $paymentMethod, object $user, ?string $type = null): \App\Models\PaymentMethod
    {
        // Implementasi disimpan di PaymentMethodService
        throw new \BadMethodCallException('StripeProvider::savePaymentMethod should be implemented by child classes');
    }

    public function initializePaymentMethod(array $data): ?array
    {
        $intent = SetupIntent::create($data);
        return [
            'client_secret' => $intent->client_secret,
            'id' => $intent->id,
        ];
    }

    public function getSupportedPaymentMethods(): array
    {
        return [
            'card',
            'ideal',
            'giropay',
            'sofort',
            'bancontact',
            'alipay',
            'wechat_pay'
        ];
    }

    public function verifyPayment(string $transactionId): array
    {
        $intent = PaymentIntent::retrieve($transactionId);
        
        return [
            'id' => $intent->id,
            'status' => $intent->status,
            'amount' => $intent->amount / 100,
            'currency' => $intent->currency,
            'payment_method_type' => $intent->payment_method_types[0] ?? null
        ];
    }
}