<?php

namespace App\Http\Controllers;

use App\DTO\ShippingData;
use App\Enums\Permission;
use App\Http\Requests\CreateShippingRequest;
use App\Http\Requests\UpdateShippingRequest;
use App\Http\Resources\ShippingResource;
use App\Models\Shipping;
use App\Services\ShippingService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShippingController extends BaseController
{
    public function __construct(private ShippingService $shippingService) {}

    public function index()
    {
        $shippings = Cache::rememberForever('shippings_all', function () {
            return $this->shippingService->getAll();
        });

        return $this->sendSuccess(ShippingResource::collection($shippings), 'Shippings retrieved');
    }

    public function store(CreateShippingRequest $request)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = ShippingData::fromRequest($request->validated());
        $shipping = $this->shippingService->create($data);
        Cache::forget('shippings_all');

        return $this->sendSuccess(new ShippingResource($shipping), 'Shipping created', 201);
    }

    public function show($id)
    {
        $shipping = $this->shippingService->findOrFail($id);

        return $this->sendSuccess(new ShippingResource($shipping), 'Shipping detail');
    }

    public function update(UpdateShippingRequest $request, $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $shipping = Shipping::findOrFail($id);
        $data = ShippingData::fromRequest($request->validated());
        $updated = $this->shippingService->update($shipping, $data);
        Cache::forget('shippings_all');

        return $this->sendSuccess(new ShippingResource($updated), 'Shipping updated');
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $shipping = Shipping::findOrFail($id);
        $this->shippingService->delete($shipping);
        Cache::forget('shippings_all');

        return $this->sendSuccess(null, 'Shipping deleted');
    }
}
