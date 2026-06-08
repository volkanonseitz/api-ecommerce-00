<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use App\Http\Requests\CheckoutVerifyRequest;
use App\DTO\CheckoutVerifyData;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkoutService) {}

    /**
     * POST /checkout/verify
     */
    public function verify(CheckoutVerifyRequest $request)
    {
        $data = CheckoutVerifyData::fromRequest($request->validated());
        $result = $this->checkoutService->verify($data, $request->user());
        return response()->json($result);
    }
}