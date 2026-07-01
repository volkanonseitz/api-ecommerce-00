<?php

declare(strict_types=1);

namespace App\Modules\Payment\Actions;

use App\Models\PaymentMethod;
use App\Modules\Payment\Contracts\PaymentGatewayFactoryInterface;
use App\Modules\PaymentMethod\DTO\PaymentMethodData;
use App\Modules\Payment\Services\PaymentMethodPersistService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

final class StorePaymentMethodAction
{
    public function __construct(
        private readonly PaymentGatewayFactoryInterface $gatewayFactory,
        private readonly PaymentMethodPersistService $persistService,
    ) {}

    public function execute(array $data, Authenticatable $user): PaymentMethod
    {
        $paymentMethodData = PaymentMethodData::fromRequest($data);
        
        $provider = $this->gatewayFactory->create($paymentMethodData->payment_gateway);

        return DB::transaction(function () use ($paymentMethodData, $user, $provider) {
            $paymentMethod = $provider->retrievePaymentMethod(
                $paymentMethodData->method_key,
                $paymentMethodData->method_type
            );

            $attachedMethod = $this->attachToCustomerIfSupported(
                $provider,
                $paymentMethodData->method_key,
                $user,
                $paymentMethodData->method_type
            );

            return $this->persistService->save(
                $attachedMethod ?? $paymentMethod,
                $user,
                $paymentMethodData
            );
        });
    }

    private function attachToCustomerIfSupported(
        object $provider,
        string $methodKey,
        Authenticatable $user,
        ?string $type
    ): ?object {
        try {
            return $provider->attachPaymentMethodToCustomer($methodKey, $user, $type);
        } catch (\BadMethodCallException $e) {
            // Provider may not support attachment
            return null;
        }
    }
}