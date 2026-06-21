<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Services\PaymentService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class PaymentIntentController extends BaseController
{
    public function __construct(private PaymentService $paymentService) {}

    public function getPaymentIntent(Request $request)
    {
        $settings = Settings::first();
        if (! $request->user() && ! ($settings->options['guestCheckout'] ?? false)) {
            throw new AuthenticationException;
        }

        return $this->paymentService->processPaymentIntent($request, $settings);
    }
}
