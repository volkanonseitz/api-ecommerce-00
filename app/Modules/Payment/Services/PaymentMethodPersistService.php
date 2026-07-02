<?php

declare(strict_types=1);

namespace App\Modules\Payment\Services;

use App\Models\PaymentMethod;
use App\Modules\PaymentMethod\DTO\PaymentMethodData;
use App\Modules\PaymentMethod\Events\PaymentMethodCreated;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

final class PaymentMethodPersistService
{
    public function save(object $paymentMethodData, Authenticatable $user, PaymentMethodData $data): PaymentMethod
    {
        $method = PaymentMethod::create([
            'payment_gateway_id' => $data->payment_gateway_id,
            'method_key' => $data->method_key,
            'method_type' => $data->method_type,
            'default_payment' => $data->default_payment,
            'fingerprint' => $data->fingerprint,
            'brand' => $data->brand,
            'last4' => $data->last4,
            'exp_month' => $data->exp_month,
            'exp_year' => $data->exp_year,
            'va_number' => $data->va_number,
            'bank_code' => $data->bank_code,
            'qris_url' => $data->qris_url,
            'ewallet_type' => $data->ewallet_type,
            'account_name' => $data->account_name,
            'account_number' => $data->account_number,
            'account_last4' => $data->account_last4,
            'metadata' => $data->metadata,
            'provider_data' => json_encode($paymentMethodData),
        ]);

        // Clear cache
        $this->clearUserCache($user);

        // Dispatch event
        Event::dispatch(new PaymentMethodCreated($method));

        return $method;
    }

    public function update(PaymentMethod $method, array $attributes): PaymentMethod
    {
        $method->update($attributes);

        // Clear cache for this user
        if ($method->paymentGateway && $method->paymentGateway->user_id) {
            Cache::forget("payment_methods.user.{$method->paymentGateway->user_id}");
        }

        return $method->fresh();
    }

    public function delete(PaymentMethod $method): void
    {
        $userId = $method->paymentGateway?->user_id;

        $method->delete();

        // Clear cache
        if ($userId) {
            Cache::forget("payment_methods.user.{$userId}");
        }
    }

    private function clearUserCache(Authenticatable $user): void
    {
        Cache::forget("payment_methods.user.{$user->id}");
    }
}
