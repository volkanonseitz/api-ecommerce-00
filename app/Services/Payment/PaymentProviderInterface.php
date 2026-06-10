<?php

namespace App\Services\Payment;

interface PaymentProviderInterface
{
    /**
     * Membuat payment intent
     */
    public function createIntent(array $data): array;

    /**
     * Membuat customer di payment gateway
     */
    public function createCustomer(array $data): array;

    /**
     * Webhook handler
     */
    public function handleWebhook(object $request): void;

    // tambahkan method lain sesuai kebutuhan
}