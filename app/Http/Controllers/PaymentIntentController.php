<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

class PaymentIntentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function getPaymentIntent(Request $request)
    {
        $settings = Settings::first();
        if (!$request->user() && !($settings->options['guestCheckout'] ?? false)) {
            throw new AuthenticationException();
        }

        return $this->paymentService->processPaymentIntent($request, $settings);
    }
}