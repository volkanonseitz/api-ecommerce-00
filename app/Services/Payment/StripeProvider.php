<?php

namespace App\Services\Payment;

use Stripe\Stripe;
use Stripe\PaymentIntent as StripePaymentIntent;
use Stripe\Customer;

class StripeProvider implements PaymentProviderInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createIntent(array $data): array
    {
        $intent = StripePaymentIntent::create([
            'amount' => $data['amount'] * 100, // dalam cent
            'currency' => config('shop.default_currency', 'usd'),
            'metadata' => ['order_tracking_number' => $data['order_tracking_number']],
            'customer' => $data['customer'] ?? null,
        ]);
        return ['client_secret' => $intent->client_secret, 'id' => $intent->id];
    }

    public function createCustomer(array $data): array
    {
        $customer = Customer::create([
            'email' => $data['email'],
            'name' => $data['name'] ?? null,
        ]);
        return ['customer_id' => $customer->id];
    }

    public function handleWebhook(object $request): void
    {
        // implement webhook
    }
}