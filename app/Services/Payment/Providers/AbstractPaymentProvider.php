<?php

declare(strict_types=1);

namespace App\Services\Payment\Providers;

use App\Services\Payment\Contracts\PaymentProviderInterface;

abstract class AbstractPaymentProvider implements PaymentProviderInterface
{
    protected string $gatewayName;

    public function getGatewayName(): string
    {
        return $this->gatewayName;
    }

    /**
     * Default implementation for optional methods.
     */
    public function retrievePaymentMethod(string $methodKey, ?string $type = null): object
    {
        throw new \BadMethodCallException('Method retrievePaymentMethod not implemented');
    }

    public function attachPaymentMethodToCustomer(string $methodKey, object $user, ?string $type = null): object
    {
        throw new \BadMethodCallException('Method attachPaymentMethodToCustomer not implemented');
    }

    public function detachPaymentMethod(string $methodKey, ?string $type = null): void
    {
        // Do nothing by default
    }

    public function savePaymentMethod(object $paymentMethod, object $user, ?string $type = null): \App\Models\PaymentMethod
    {
        throw new \BadMethodCallException('Method savePaymentMethod not implemented');
    }

    public function initializePaymentMethod(array $data): ?array
    {
        return null;
    }

    public function getSupportedPaymentMethods(): array
    {
        return [];
    }

    public function verifyPayment(string $transactionId): array
    {
        throw new \BadMethodCallException('Method verifyPayment not implemented');
    }
}