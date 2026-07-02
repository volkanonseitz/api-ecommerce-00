<?php

declare(strict_types=1);

namespace App\Modules\Payment\Actions;

use App\Models\PaymentMethod;
use App\Modules\Payment\Contracts\PaymentGatewayFactoryInterface;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;

final class DeletePaymentMethodAction
{
    public function __construct(
        private readonly PaymentGatewayFactoryInterface $gatewayFactory,
        private readonly LoggerInterface $logger,
    ) {}

    public function execute(PaymentMethod $method): void
    {
        DB::transaction(function () use ($method) {
            $provider = $this->gatewayFactory->create($method->paymentGateway->gateway_name);

            try {
                $provider->detachPaymentMethod($method->method_key, $method->method_type);
            } catch (\Exception $e) {
                $this->logger->warning('Failed to detach payment method from gateway', [
                    'method_id' => $method->id,
                    'gateway' => $method->paymentGateway->gateway_name,
                    'error' => $e->getMessage(),
                ]);
            }

            $method->forceDelete();
        });
    }
}
