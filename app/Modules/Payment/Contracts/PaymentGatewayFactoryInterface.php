<?php

declare(strict_types=1);

namespace App\Modules\Payment\Contracts;

interface PaymentGatewayFactoryInterface
{
    /**
     * Create a payment provider instance for the given gateway.
     *
     * @throws \InvalidArgumentException if gateway is not supported
     */
    public function create(string $gateway): PaymentProviderInterface;

    /**
     * Get all available gateways.
     *
     * @return array<string>
     */
    public function getAvailableGateways(): array;

    /**
     * Check if a gateway is supported.
     */
    public function supports(string $gateway): bool;
}