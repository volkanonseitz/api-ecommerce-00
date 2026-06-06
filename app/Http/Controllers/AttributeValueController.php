<?php

namespace App\Http\Controllers;

use App\Services\AttributeValueService;
use App\Http\Requests\AttributeValueRequest;
use App\Http\Resources\AttributeValueResource;
use App\DTO\AttributeValueData;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\AttributeValue;

class AttributeValueController extends Controller
{
    public function __construct(private AttributeValueService $attributeValueService) {}

    /**
     * GET /attribute-values
     */
    public function index()
    {
        $values = $this->attributeValueService->getAllAttributeValues();
        return AttributeValueResource::collection($values);
    }

    /**
     * POST /attribute-values
     */
    public function store(AttributeValueRequest $request)
    {
        $user = $request->user();
        $shopId = $request->shop_id;
        if (!$this->attributeValueService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = AttributeValueData::fromRequest($request->validated());
        $value = $this->attributeValueService->createAttributeValue($data);
        return new AttributeValueResource($value);
    }

    /**
     * GET /attribute-values/{id}
     */
    public function show($id)
    {
        $value = $this->attributeValueService->getAttributeValueById($id);
        return new AttributeValueResource($value);
    }

    /**
     * PUT /attribute-values/{id}
     */
    public function update(AttributeValueRequest $request, $id)
    {
        $value = AttributeValue::findOrFail($id);
        $user = $request->user();
        $shopId = $request->shop_id ?? $value->shop_id;
        if (!$this->attributeValueService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = AttributeValueData::fromRequest($request->validated());
        $updated = $this->attributeValueService->updateAttributeValue($value, $data);
        return new AttributeValueResource($updated);
    }

    /**
     * DELETE /attribute-values/{id}
     */
    public function destroy(Request $request, $id)
    {
        $value = AttributeValue::findOrFail($id);
        $user = $request->user();
        $shopId = $value->shop_id;
        if (!$this->attributeValueService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $this->attributeValueService->deleteAttributeValue($value);
        return response()->json(['message' => 'Attribute value deleted successfully']);
    }
}