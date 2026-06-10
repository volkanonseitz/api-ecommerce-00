<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Services\Payment\FlutterwaveProvider;
use Illuminate\Http\Request;

class WebHookController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function stripe(Request $request)
    {
        $this->paymentService->handleWebhook('stripe', $request);
        return response()->json(['status' => 'success']);
    }

    public function paypal(Request $request)
    {
        $this->paymentService->handleWebhook('paypal', $request);
        return response()->json(['status' => 'success']);
    }

    public function razorpay(Request $request)
    {
        $this->paymentService->handleWebhook('razorpay', $request);
        return response()->json(['status' => 'success']);
    }

    public function mollie(Request $request)
    {
        $this->paymentService->handleWebhook('mollie', $request);
        return response()->json(['status' => 'success']);
    }

    public function paystack(Request $request)
    {
        $this->paymentService->handleWebhook('paystack', $request);
        return response()->json(['status' => 'success']);
    }

    public function paymongo(Request $request)
    {
        $this->paymentService->handleWebhook('paymongo', $request);
        return response()->json(['status' => 'success']);
    }

    public function xendit(Request $request)
    {
        $this->paymentService->handleWebhook('xendit', $request);
        return response()->json(['status' => 'success']);
    }

    public function iyzico(Request $request)
    {
        $this->paymentService->handleWebhook('iyzico', $request);
        return response()->json(['status' => 'success']);
    }

    public function bkash(Request $request)
    {
        $this->paymentService->handleWebhook('bkash', $request);
        return response()->json(['status' => 'success']);
    }

    public function flutterwave(Request $request)
    {
        $this->paymentService->handleWebhook('flutterwave', $request);
        return response()->json(['status' => 'success']);
    }

    public function callback(Request $request)
    {
        $flutterwave = new FlutterwaveProvider();
        return $flutterwave->handleCallback($request);
    }
}