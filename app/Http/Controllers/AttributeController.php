<?php

namespace App\Http\Controllers;

use App\Services\AttributeService;
use App\Http\Requests\AttributeRequest;
use App\Http\Resources\AttributeResource;
use App\DTO\AttributeData;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\Attribute;
use App\Enums\Permission;
use Illuminate\Support\Facades\Cache;

class AttributeController extends BaseController
{
    public function __construct(private AttributeService $attributeService) {}

    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $cacheKey = "attributes_{$language}";
        $attributes = Cache::remember($cacheKey, 3600, function () use ($language) {
            return $this->attributeService->getAttributesByLanguage($language);
        });
        return $this->sendSuccess(AttributeResource::collection($attributes), 'Attributes retrieved');
    }

    public function store(AttributeRequest $request)
    {
        $user = $request->user();
        $shopId = $request->shop_id;
        if (!$this->attributeService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = AttributeData::fromRequest($request->validated());
        $attribute = $this->attributeService->createAttribute($data);
        Cache::forget("attributes_{$data->language}");
        return $this->sendSuccess(new AttributeResource($attribute), 'Attribute created', 201);
    }

    public function show(Request $request, $identifier)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $attribute = $this->attributeService->getAttributeByIdOrSlug($identifier, $language);
        return $this->sendSuccess(new AttributeResource($attribute), 'Attribute detail');
    }

    public function update(AttributeRequest $request, $id)
    {
        $user = $request->user();
        $shopId = $request->shop_id;
        if (!$this->attributeService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $attribute = Attribute::findOrFail($id);
        $data = AttributeData::fromRequest($request->validated());
        $updated = $this->attributeService->updateAttribute($attribute, $data);
        Cache::forget("attributes_{$data->language}");
        return $this->sendSuccess(new AttributeResource($updated), 'Attribute updated');
    }

    public function destroy(Request $request, $id)
    {
        $attribute = Attribute::findOrFail($id);
        $user = $request->user();
        $shopId = $attribute->shop_id;
        if (!$this->attributeService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $language = $attribute->language;
        $this->attributeService->deleteAttribute($attribute);
        Cache::forget("attributes_{$language}");
        return $this->sendSuccess(null, 'Attribute deleted');
    }

    // export dan import tetap sama, hanya ubah response ke sendSuccess
    public function exportAttributes(Request $request, $shopId)
    {
        // ... tidak diubah karena streaming
    }

    public function importAttributes(Request $request)
    {
        // ... gunakan sendSuccess
    }
}