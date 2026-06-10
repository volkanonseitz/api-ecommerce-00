<?php

namespace App\Http\Controllers;

use App\Services\ShippingService;
use App\Http\Requests\CreateShippingRequest;
use App\Http\Requests\UpdateShippingRequest;
use App\Http\Resources\ShippingResource;
use App\DTO\ShippingData;
use App\Models\Shipping;

class ShippingController extends Controller
{
    public function __construct(private ShippingService $shippingService) {}

    /**
     * GET /shippings
     */
    public function index()
    {
        $shippings = $this->shippingService->getAll();
        return ShippingResource::collection($shippings);
    }

    /**
     * POST /shippings
     */
    public function store(CreateShippingRequest $request)
    {
        $data = ShippingData::fromRequest($request->validated());
        $shipping = $this->shippingService->create($data);
        return new ShippingResource($shipping);
    }

    /**
     * GET /shippings/{id}
     */
    public function show($id)
    {
        $shipping = $this->shippingService->findOrFail($id);
        return new ShippingResource($shipping);
    }

    /**
     * PUT /shippings/{id}
     */
    public function update(UpdateShippingRequest $request, $id)
    {
        $shipping = Shipping::findOrFail($id);
        $data = ShippingData::fromRequest($request->validated());
        $updated = $this->shippingService->update($shipping, $data);
        return new ShippingResource($updated);
    }

    /**
     * DELETE /shippings/{id}
     */
    public function destroy($id)
    {
        $shipping = Shipping::findOrFail($id);
        $this->shippingService->delete($shipping);
        return response()->json(['message' => 'Shipping deleted successfully']);
    }
}