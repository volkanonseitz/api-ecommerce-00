<?php

namespace App\Http\Controllers;

use App\Services\RefundReasonService;
use App\Http\Requests\RefundReasonCreateRequest;
use App\Http\Requests\RefundReasonUpdateRequest;
use App\Http\Resources\RefundReasonResource;
use App\DTO\RefundReasonData;
use App\Models\RefundReason;
use Illuminate\Http\Request;

class RefundReasonController extends Controller
{
    public function __construct(private RefundReasonService $service) {}

    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $limit = $request->limit ?? 15;
        $reasons = $this->service->getRefundReasons($language, $limit);
        return RefundReasonResource::collection($reasons);
    }

    public function store(RefundReasonCreateRequest $request)
    {
        // Asli tidak ada permission check, jadi hapus
        $data = RefundReasonData::fromRequest($request->validated());
        $reason = $this->service->create($data);
        return new RefundReasonResource($reason);
    }

    public function show(Request $request, $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $reason = $this->service->find($params, $language);
        return new RefundReasonResource($reason);
    }

    public function update(RefundReasonUpdateRequest $request, $id)
    {
        $reason = RefundReason::findOrFail($id);
        $data = RefundReasonData::fromRequest($request->validated());
        $updated = $this->service->update($reason, $data);
        return new RefundReasonResource($updated);
    }

    public function destroy(Request $request, $id)
    {
        $reason = RefundReason::findOrFail($id);
        $this->service->delete($reason);
        return response()->json(['message' => 'Refund reason deleted successfully']);
    }
}