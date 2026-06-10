<?php

namespace App\Http\Controllers;

use App\Services\FlashSaleVendorService;
use App\Http\Requests\FlashSaleVendorCreateRequest;
use App\Http\Requests\FlashSaleVendorUpdateRequest;
use App\Http\Resources\FlashSaleRequestResource;
use App\DTO\FlashSaleRequestData;
use App\Models\FlashSaleRequest;
use App\Enums\Permission;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;

class FlashSaleVendorController extends Controller
{
    public function __construct(private FlashSaleVendorService $service) {}

    public function index(Request $request)
    {
        $limit = $request->limit ?? 10;
        $requests = $this->service->getRequestsQuery($request)->paginate($limit);
        return FlashSaleRequestResource::collection($requests);
    }

    public function store(FlashSaleVendorCreateRequest $request)
    {
        $user = $request->user();
        if (!$this->service->hasPermission($user)) {
            throw new AuthorizationException(config('constants.NOT_AUTHORIZED'));
        }
        $data = FlashSaleRequestData::fromRequest($request->validated(), $request->language);
        $flashSaleRequest = $this->service->create($data);
        return new FlashSaleRequestResource($flashSaleRequest);
    }

    public function show(Request $request, $id)
    {
        $language = $request->language ?? config('constants.DEFAULT_LANGUAGE', 'en');
        $flashSaleRequest = FlashSaleRequest::where('language', $language)->where('id', $id)->firstOrFail();
        return new FlashSaleRequestResource($flashSaleRequest);
    }

    public function update(FlashSaleVendorUpdateRequest $request, $id)
    {
        $user = $request->user();
        if (!$this->service->hasPermission($user)) {
            throw new AuthorizationException(config('constants.NOT_AUTHORIZED'));
        }
        $flashSaleRequest = FlashSaleRequest::findOrFail($id);
        $data = FlashSaleRequestData::fromRequest($request->validated(), $request->language);
        $updated = $this->service->update($flashSaleRequest, $data);
        return new FlashSaleRequestResource($updated);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->service->hasPermission($user)) {
            throw new AuthorizationException(config('constants.NOT_AUTHORIZED'));
        }
        $flashSaleRequest = FlashSaleRequest::findOrFail($id);
        $this->service->delete($flashSaleRequest, $user);
        return response()->json(['message' => 'Flash sale request deleted']);
    }

    public function approveFlashSaleProductsRequest(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('constants.NOT_AUTHORIZED'));
        }
        $this->service->approveRequest($request->id);
        return response()->json(['message' => 'Request approved']);
    }

    public function disapproveFlashSaleProductsRequest(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('constants.NOT_AUTHORIZED'));
        }
        $this->service->disapproveRequest($request->id);
        return response()->json(['message' => 'Request disapproved']);
    }

    public function getRequestedProductsForFlashSale(Request $request)
    {
        $request->validate(['vendor_request_id' => 'required|exists:flash_sale_requests,id']);
        $limit = $request->limit ?? 10;
        $products = $this->service->getRequestedProductsQuery($request, $request->vendor_request_id)->paginate($limit);
        return ProductResource::collection($products);
    }
}