<?php

namespace App\Http\Controllers;

use App\DTO\DeliveryTimeData;
use App\Enums\Permission;
use App\Http\Requests\DeliveryTimeRequest;
use App\Models\DeliveryTime;
use App\Services\DeliveryTimeService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DeliveryTimeController extends BaseController
{
    public function __construct(private DeliveryTimeService $deliveryTimeService) {}

    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $cacheKey = "delivery_times_{$language}";
        $deliveryTimes = Cache::rememberForever($cacheKey, function () use ($language) {
            return $this->deliveryTimeService->getAll($language);
        });

        return $this->sendSuccess($deliveryTimes, 'Delivery times retrieved');
    }

    public function store(DeliveryTimeRequest $request)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = DeliveryTimeData::fromRequest($request->validated());
        $deliveryTime = $this->deliveryTimeService->create($data);
        Cache::forget("delivery_times_{$data->language}");

        return $this->sendSuccess($deliveryTime, 'Delivery time created', 201);
    }

    public function show(Request $request, $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $deliveryTime = $this->deliveryTimeService->find($params, $language);

        return $this->sendSuccess($deliveryTime, 'Delivery time detail');
    }

    public function update(DeliveryTimeRequest $request, $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $deliveryTime = DeliveryTime::findOrFail($id);
        $data = DeliveryTimeData::fromRequest($request->validated());
        $updated = $this->deliveryTimeService->update($deliveryTime, $data);
        Cache::forget("delivery_times_{$data->language}");

        return $this->sendSuccess($updated, 'Delivery time updated');
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $deliveryTime = DeliveryTime::findOrFail($id);
        $language = $deliveryTime->language;
        $this->deliveryTimeService->delete($deliveryTime);
        Cache::forget("delivery_times_{$language}");

        return $this->sendSuccess(null, 'Delivery time deleted');
    }
}
