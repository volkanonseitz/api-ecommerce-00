<?php

namespace App\Http\Controllers;

use App\Services\DeliveryTimeService;
use App\Http\Requests\DeliveryTimeRequest;
use App\DTO\DeliveryTimeData;
use App\Models\DeliveryTime;
use Illuminate\Http\Request;

class DeliveryTimeController extends Controller
{
    public function __construct(private DeliveryTimeService $deliveryTimeService) {}

    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'en');
        $deliveryTimes = $this->deliveryTimeService->getAll($language);
        return response()->json($deliveryTimes);
    }

    public function store(DeliveryTimeRequest $request)
    {
        $data = DeliveryTimeData::fromRequest($request->validated());
        $deliveryTime = $this->deliveryTimeService->create($data);
        return response()->json($deliveryTime);
    }

    public function show(Request $request, $params)
    {
        $language = $request->language ?? config('shop.default_language', 'en');
        $deliveryTime = $this->deliveryTimeService->find($params, $language);
        return response()->json($deliveryTime);
    }

    public function update(DeliveryTimeRequest $request, $id)
    {
        $deliveryTime = DeliveryTime::findOrFail($id);
        $data = DeliveryTimeData::fromRequest($request->validated());
        $updated = $this->deliveryTimeService->update($deliveryTime, $data);
        return response()->json($updated);
    }

    public function destroy($id)
    {
        $deliveryTime = DeliveryTime::findOrFail($id);
        $this->deliveryTimeService->delete($deliveryTime);
        return response()->json(['message' => 'Delivery time deleted successfully']);
    }
}