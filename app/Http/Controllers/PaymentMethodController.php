<?php

namespace App\Http\Controllers;

use App\Services\PaymentMethodService;
use App\Http\Requests\PaymentMethodCreateRequest;
use App\DTO\PaymentMethodData;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function __construct(private PaymentMethodService $pmService) {}

    public function index(Request $request)
    {
        return $this->pmService->getUserPaymentMethods($request->user());
    }

    public function store(PaymentMethodCreateRequest $request)
    {
        $data = PaymentMethodData::fromRequest($request->validated());
        $method = $this->pmService->storeCard($data, $request->user());
        return response()->json($method);
    }

    public function destroy(Request $request, $id)
    {
        $this->pmService->deletePaymentMethod($id);
        return response()->json(['message' => 'Payment method deleted']);
    }

    public function savePaymentMethod(Request $request)
    {
        if ($request->payment_gateway === 'stripe') {
            return $this->pmService->saveStripeCard($request);
        }
        return response()->json(['error' => 'Unsupported gateway'], 400);
    }

    public function saveCardIntent(Request $request)
    {
        return $this->pmService->createSetupIntent($request->user());
    }

    public function setDefaultCard(Request $request)
    {
        $method = $this->pmService->setDefaultCard($request->method_id);
        return response()->json($method);
    }
}