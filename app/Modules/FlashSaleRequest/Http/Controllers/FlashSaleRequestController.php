<?php

declare(strict_types=1);

namespace App\Modules\FlashSaleRequest\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\FlashSaleRequest;
use App\Modules\FlashSaleRequest\DTO\FlashSaleRequestData;
use App\Modules\FlashSaleRequest\Http\Requests\FlashSaleRequestCreateRequest;
use App\Modules\FlashSaleRequest\Http\Requests\FlashSaleRequestUpdateRequest;
use App\Modules\FlashSaleRequest\Http\Resources\FlashSaleRequestResource;
use App\Modules\FlashSaleRequest\Services\FlashSaleRequestService;
use App\Modules\Product\Http\Resources\ProductResource;
use Illuminate\Http\Request;

class FlashSaleRequestController extends BaseController
{
    public function __construct(private FlashSaleRequestService $service) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', FlashSaleRequest::class);
        $limit = (int) ($request->limit ?? 10);
        $requests = $this->service->getRequestsQuery($request)->paginate($limit);

        return FlashSaleRequestResource::collection($requests);
    }

    public function store(FlashSaleRequestCreateRequest $request)
    {
        $this->authorize('create', FlashSaleRequest::class);
        $data = FlashSaleRequestData::fromRequest($request->validated(), $request->language);
        $flashSaleRequest = $this->service->create($data);

        return new FlashSaleRequestResource($flashSaleRequest);
    }

    public function show(Request $request, int $id)
    {
        $flashSaleRequest = $this->service->findOrFail($id);
        $this->authorize('view', $flashSaleRequest);

        return new FlashSaleRequestResource($flashSaleRequest);
    }

    public function update(FlashSaleRequestUpdateRequest $request, int $id)
    {
        $flashSaleRequest = $this->service->findOrFail($id);
        $this->authorize('update', $flashSaleRequest);
        $data = FlashSaleRequestData::fromRequest($request->validated(), $request->language);
        $updated = $this->service->update($flashSaleRequest, $data);

        return new FlashSaleRequestResource($updated);
    }

    public function destroy(Request $request, int $id)
    {
        $flashSaleRequest = $this->service->findOrFail($id);
        $this->authorize('delete', $flashSaleRequest);
        $this->service->delete($flashSaleRequest, $request->user());

        return $this->sendSuccess(null, 'Flash sale request deleted successfully');
    }

    public function approveFlashSaleProductsRequest(Request $request)
    {
        $this->authorize('approve', FlashSaleRequest::class);
        $request->validate(['id' => 'required|exists:flash_sale_requests,id']);
        $this->service->approveRequest($request->id);

        return $this->sendSuccess(null, 'Request approved successfully');
    }

    public function disapproveFlashSaleProductsRequest(Request $request)
    {
        $this->authorize('disapprove', FlashSaleRequest::class);
        $request->validate(['id' => 'required|exists:flash_sale_requests,id']);
        $this->service->disapproveRequest($request->id);

        return $this->sendSuccess(null, 'Request disapproved successfully');
    }

    public function getRequestedProductsForFlashSale(Request $request)
    {
        $request->validate(['vendor_request_id' => 'required|exists:flash_sale_requests,id']);
        $limit = (int) ($request->limit ?? 10);
        $products = $this->service->getRequestedProductsQuery($request, $request->vendor_request_id)->paginate($limit);

        return ProductResource::collection($products);
    }
}
