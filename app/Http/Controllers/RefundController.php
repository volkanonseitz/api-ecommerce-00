<?php

namespace App\Http\Controllers;

use App\Services\RefundService;
use App\Http\Requests\RefundRequest;
use App\Http\Resources\RefundResource;
use App\Http\Resources\GetSingleRefundResource;
use App\DTO\RefundData;
use App\Models\Refund;
use App\Enums\Permission;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;

class RefundController extends Controller
{
    public function __construct(private RefundService $refundService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        $limit = $request->limit ?? 15;
        $refunds = $this->refundService->getRefundsQuery($request, $user)->paginate($limit);
        $data = RefundResource::collection($refunds)->response()->getData(true);
        return formatAPIResourcePaginate($data);
    }

    public function store(RefundRequest $request)
    {
        $user = $request->user();
        if (!$user) throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        $data = RefundData::fromRequest($request->validated(), $user->id);
        $refund = $this->refundService->storeRefund($data);
        return new RefundResource($refund);
    }

    public function show($id)
    {
        $refund = Refund::with(['shop', 'order', 'customer', 'refundPolicy', 'refundReason'])->findOrFail($id);
        return new GetSingleRefundResource($refund);
    }

    public function update(RefundRequest $request, $id)
    {
        $refund = Refund::findOrFail($id);
        $user = $request->user();
        if (!$this->refundService->hasPermission($user, $refund->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = RefundData::fromRequest($request->validated(), $refund->customer_id, $refund->shop_id);
        $updated = $this->refundService->updateRefund($refund, $data);
        return new RefundResource($updated);
    }

    public function destroy(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);
        if (!$this->refundService->hasPermission($request->user(), $refund->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $this->refundService->deleteRefund($refund);
        return response()->json(['message' => 'Refund deleted']);
    }
}