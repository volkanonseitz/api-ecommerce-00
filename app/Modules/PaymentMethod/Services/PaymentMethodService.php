<?php

declare(strict_types=1);

namespace App\Modules\PaymentMethod\Services;

use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Modules\Payment\Factory\PaymentProviderFactory;
use App\Modules\PaymentMethod\DTO\PaymentMethodData;
use App\Modules\PaymentMethod\Events\PaymentMethods;
use App\Services\Payment\Contracts\PaymentProviderInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PaymentMethodService
{
    /**
     * Get all payment methods for a user.
     *
     * @param  Authenticatable&object{id: int}  $user
     * @return Collection<int, PaymentMethod>
     */
    public function getUserPaymentMethods(Authenticatable $user): Collection
    {
        return PaymentMethod::whereHas('paymentGateway', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('paymentGateway')->get();
    }

    /**
     * Get payment methods by gateway for a user.
     *
     * @param  Authenticatable&object{id: int}  $user
     * @return Collection<int, PaymentMethod>
     */
    public function getUserPaymentMethodsByGateway(Authenticatable $user, string $gateway): Collection
    {
        return PaymentMethod::whereHas('paymentGateway', function ($q) use ($user, $gateway) {
            $q->where('user_id', $user->id)
                ->where('gateway_name', strtolower($gateway));
        })->with('paymentGateway')->get();
    }

    /**
     * Get payment methods by type for a user.
     *
     * @param  Authenticatable&object{id: int}  $user
     * @return Collection<int, PaymentMethod>
     */
    public function getUserPaymentMethodsByType(Authenticatable $user, string $type): Collection
    {
        return PaymentMethod::whereHas('paymentGateway', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('method_type', $type)->with('paymentGateway')->get();
    }

    /**
     * Store a new payment method.
     *
     * @param  Authenticatable&object{id: int, email: string, name: string}  $user
     */
    public function storePaymentMethod(PaymentMethodData $data, Authenticatable $user): PaymentMethod
    {
        $provider = PaymentProviderFactory::create($data->payment_gateway);

        // Retrieve the payment method from gateway
        $paymentMethod = $provider->retrievePaymentMethod($data->method_key, $data->method_type);

        // Optionally attach to customer if needed (some providers auto-attach)
        try {
            $attached = $provider->attachPaymentMethodToCustomer($data->method_key, $user, $data->method_type);
        } catch (\BadMethodCallException $e) {
            // Some providers may not support attachment
            $attached = null;
        }

        // Save to local DB
        $methodToSave = $paymentMethod;
        if ($attached !== null) {
            $methodToSave = $attached;
        }

        return $provider->savePaymentMethod($methodToSave, $user, $data->method_type);
    }

    /**
     * Save payment method from request.
     */
    public function savePaymentMethod(Request $request): PaymentMethod
    {
        $data = PaymentMethodData::fromRequest($request->all());

        return $this->storePaymentMethod($data, $request->user());
    }

    /**
     * Set a payment method as default.
     */
    public function setDefaultPayment(int $methodId): PaymentMethod
    {
        $method = PaymentMethod::findOrFail($methodId);

        PaymentMethod::where('payment_gateway_id', $method->payment_gateway_id)
            ->where('id', '!=', $methodId)
            ->update(['default_payment' => false]);

        $method->default_payment = true;
        $method->save();

        event(new PaymentMethods($method));

        return $method->fresh();
    }

    /**
     * Delete a payment method.
     */
    public function deletePaymentMethod(int $id): void
    {
        $method = PaymentMethod::findOrFail($id);
        /** @var PaymentGateway $paymentGateway */
        $paymentGateway = $method->paymentGateway;
        $provider = PaymentProviderFactory::create($paymentGateway->gateway_name);
        $provider->detachPaymentMethod($method->method_key, $method->method_type);
        $method->forceDelete();
    }

    /**
     * Initialize a payment method for adding.
     *
     * @param  Authenticatable&object{id: int, email: string, name: string}  $user
     */
    public function initializePaymentMethod(Authenticatable $user, string $gateway = 'stripe', array $options = []): ?array
    {
        $provider = PaymentProviderFactory::create($gateway);

        // Check if gateway requires customer creation
        $customerId = null;
        if ($provider->getGatewayName() === 'stripe' || $provider->getGatewayName() === 'xendit') {
            $customer = $provider->createCustomer([
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                ...$options,
            ]);
            $customerId = $customer['customer_id'];
        }

        return $provider->initializePaymentMethod([
            'customer_id' => $customerId,
            ...$options,
        ]);
    }

    /**
     * Get supported payment methods for a gateway.
     */
    public function getSupportedPaymentMethods(string $gateway): array
    {
        $provider = PaymentProviderFactory::create($gateway);

        return $provider->getSupportedPaymentMethods();
    }

    /**
     * Create a payment with specific payment method type.
     */
    public function createPayment(array $data, string $gateway, Authenticatable $user): array
    {
        $provider = PaymentProviderFactory::create($gateway);

        // Add customer info if not provided
        if (! isset($data['customer_id']) && $user) {
            // Get or create customer
            $customer = $provider->createCustomer([
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ]);
            $data['customer_id'] = $customer['customer_id'];
        }

        return $provider->createPayment($data);
    }

    /**
     * Get the provider for a specific gateway.
     */
    public function getProvider(string $gateway): PaymentProviderInterface
    {
        return PaymentProviderFactory::create($gateway);
    }

    /**
     * Get all available gateways.
     */
    public function getAvailableGateways(): array
    {
        return PaymentProviderFactory::getAvailableGateways();
    }
}
