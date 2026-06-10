<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\DTO\PaymentMethodData;
use App\Events\PaymentMethods;
use App\Services\Payment\StripeProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class PaymentMethodService
{
    protected StripeProvider $stripe;

    public function __construct()
    {
        $this->stripe = new StripeProvider();
    }

    public function getUserPaymentMethods(Authenticatable $user)
    {
        return PaymentMethod::whereHas('paymentGateway', fn($q) => $q->where('user_id', $user->id)->where('gateway_name', 'stripe'))
            ->with('paymentGateway')->get();
    }

    public function storeCard(PaymentMethodData $data, Authenticatable $user): PaymentMethod
    {
        $retrieved = $this->stripe->retrievePaymentMethod($data->method_key);
        $existing = PaymentMethod::where('fingerprint', $retrieved->card->fingerprint)->first();
        if ($existing) return $existing;

        $attached = $this->stripe->attachPaymentMethodToCustomer($retrieved->id, $user);
        return $this->stripe->saveCard($attached, $user);
    }

    public function saveStripeCard(Request $request): PaymentMethod
    {
        return $this->storeCard(PaymentMethodData::fromRequest($request->all()), $request->user());
    }

    public function setDefaultCard(int $methodId): PaymentMethod
    {
        $method = PaymentMethod::findOrFail($methodId);
        PaymentMethod::where('payment_gateway_id', $method->payment_gateway_id)
            ->where('id', '!=', $methodId)->update(['default_card' => false]);
        $method->default_card = true;
        $method->save();
        event(new PaymentMethods($method));
        return $method;
    }

    public function deletePaymentMethod(int $id): void
    {
        $method = PaymentMethod::findOrFail($id);
        $this->stripe->detachPaymentMethodToCustomer($method->method_key);
        $method->forceDelete();
    }

    public function createSetupIntent(Authenticatable $user): array
    {
        $customer = $this->stripe->createCustomer($user);
        return $this->stripe->setIntent([
            'customer' => $customer['customer_id'],
            'payment_method_types' => ['card'],
            'usage' => 'on_session',
        ]);
    }
}