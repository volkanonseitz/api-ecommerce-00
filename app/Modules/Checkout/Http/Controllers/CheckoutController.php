<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Modules\Checkout\DTO\CheckoutVerifyData;
use App\Modules\Checkout\Http\Requests\CheckoutVerifyRequest;
use App\Modules\Checkout\Services\CheckoutService;
use Illuminate\Http\JsonResponse;

class CheckoutController extends BaseController
{
    public function __construct(private CheckoutService $checkoutService) {}

    public function verify(CheckoutVerifyRequest $request): JsonResponse
    {
        $this->authorize('verify-checkout');

        $data = CheckoutVerifyData::fromRequest($request->validated());
        $result = $this->checkoutService->verify($data, $request->user());

        return response()->json($result);
    }
}
