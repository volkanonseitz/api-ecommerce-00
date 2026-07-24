<?php

declare(strict_types=1);

namespace App\Modules\PaymentMethod\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\PaymentMethod;
use App\Modules\PaymentMethod\DTO\PaymentMethodData;
use App\Modules\PaymentMethod\Http\Requests\PaymentMethodCreateRequest;
use App\Modules\PaymentMethod\Http\Requests\SavePaymentMethodRequest;
use App\Modules\PaymentMethod\Http\Requests\SetDefaultCardRequest;
use App\Modules\PaymentMethod\Http\Resources\PaymentMethodResource;
use App\Modules\PaymentMethod\Services\PaymentMethodService;
use Illuminate\Http\Request;

class PaymentMethodController extends BaseController
{
    public function __construct(private PaymentMethodService $pmService) {}

    /**
     * GET /payment-methods
     */
    public function index(Request $request)
    {
        $gateway = $request->query('gateway');
        if ($gateway) {
            $methods = $this->pmService->getUserPaymentMethodsByGateway($request->user(), $gateway);
        } else {
            $methods = $this->pmService->getUserPaymentMethods($request->user());
        }

        return PaymentMethodResource::collection($methods);
    }

    public function show(int $id)
    {
        $method = PaymentMethod::findOrFail($id); // Assuming ID is unique for settings
        $this->authorize('view', $method);

        return new PaymentMethodResource($method);
    }

    /**
     * GET /payment-methods/gateways
     */
    public function gateways(Request $request)
    {
        return response()->json([
            'gateways' => $this->pmService->getAvailableGateways(),
        ]);
    }

    /**
     * POST /payment-methods
     */
    public function store(PaymentMethodCreateRequest $request)
    {
        $this->authorize('create', PaymentMethod::class);

        $data = PaymentMethodData::fromRequest($request->validated());
        $method = $this->pmService->storeCard($data, $request->user());

        return new PaymentMethodResource($method);
    }

    /**
     * DELETE /payment-methods/{id}
     */
    public function destroy(Request $request, int $id)
    {
        $method = PaymentMethod::findOrFail($id);
        $this->authorize('delete', $method);

        $this->pmService->deletePaymentMethod($id);

        return $this->sendSuccess(null, 'Payment method deleted successfully');
    }

    /**
     * POST /payment-methods/save
     */
    public function savePaymentMethod(SavePaymentMethodRequest $request)
    {
        $this->authorize('create', PaymentMethod::class);

        $method = $this->pmService->savePaymentMethod($request);

        return new PaymentMethodResource($method);
    }

    /**
     * POST /payment-methods/setup-intent
     */
    public function saveCardIntent(Request $request)
    {
        $this->authorize('create', PaymentMethod::class);

        $request->validate([
            'gateway' => ['nullable', 'string', 'in:stripe,midtrans,xendit'],
        ]);

        $gateway = $request->input('gateway', 'xendit');
        $intent = $this->pmService->createSetupIntent($request->user(), $gateway);

        return response()->json($intent ?? ['status' => 'not_supported']);
    }

    /**
     * POST /payment-methods/set-default
     */
    public function setDefaultCard(SetDefaultCardRequest $request)
    {
        $method = $this->pmService->setDefaultCard($request->method_id);
        $this->authorize('setDefault', $method);

        return new PaymentMethodResource($method);
    }
}
