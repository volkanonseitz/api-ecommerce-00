<?php

declare(strict_types=1);

namespace App\Modules\Payment\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface PaymentProviderInterface
{
    /**
     * Create a payment transaction.
     */
    public function createPayment(array $data): array;

    /**
     * Create a customer in the gateway.
     */
    public function createCustomer(array $data): array;

    /**
     * Retrieve a payment method by its identifier.
     */
    public function retrievePaymentMethod(string $methodKey, ?string $type = null): object;

    /**
     * Attach payment method to a customer.
     */
    public function attachPaymentMethodToCustomer(string $methodKey, Authenticatable $user, ?string $type = null): object;

    /**
     * Detach payment method from customer.
     */
    public function detachPaymentMethod(string $methodKey, ?string $type = null): void;

    /**
     * Initialize a payment method for adding.
     */
    public function initializePaymentMethod(array $data): ?array;

    /**
     * Handle webhook from gateway.
     */
    public function handleWebhook(object $request): void;

    /**
     * Get the gateway name.
     */
    public function getGatewayName(): string;

    /**
     * Get supported payment method types.
     */
    public function getSupportedPaymentMethods(): array;

    /**
     * Verify a payment transaction.
     */
    public function verifyPayment(string $transactionId): array;
}
