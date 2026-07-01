<?php

declare(strict_types=1);

namespace App\Modules\Refund\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Refund;
use App\Modules\Refund\DTO\RefundData;
use App\Modules\Refund\Http\Requests\RefundRequest;
use App\Modules\Refund\Http\Resources\GetSingleRefundResource;
use App\Modules\Refund\Http\Resources\RefundResource;
use App\Modules\Refund\Services\RefundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefundController extends BaseController
{
    public function __construct(private RefundService $refundService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Refund::class);
        
        $limit = (int) $request->get('limit', 15);
        $refunds = $this->refundService->getRefundsQuery($request, $request->user())->paginate($limit);

        return $this->sendPaginated(
            $refunds,
            RefundResource::collection($refunds->getCollection()),
            'Refunds retrieved successfully'
        );
    }

    public function store(RefundRequest $request): JsonResponse
    {
        $this->authorize('create', Refund::class);
        
        $data = RefundData::fromRequest($request);
        $refund = $this->refundService->storeRefund($data, $request->user());

        return $this->sendSuccess(
            new RefundResource($refund),
            'Refund created successfully',
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $refund = Refund::with(['shop', 'order', 'customer', 'refundPolicy', 'refundReason'])->findOrFail($id);
        
        $this->authorize('view', $refund);

        return $this->sendSuccess(
            new GetSingleRefundResource($refund),
            'Refund retrieved successfully'
        );
    }

    public function update(RefundRequest $request, int $id): JsonResponse
    {
        $refund = Refund::findOrFail($id);
        $this->authorize('update', $refund);
        
        $data = RefundData::fromRequest($request);
        $updated = $this->refundService->updateRefund($refund, $data, $request->user());

        return $this->sendSuccess(
            new RefundResource($updated),
            'Refund updated successfully'
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $refund = Refund::findOrFail($id);
        $this->authorize('delete', $refund);
        
        $this->refundService->deleteRefund($refund, $request->user());

        return $this->sendSuccess(
            null,
            'Refund deleted successfully'
        );
    }
}
