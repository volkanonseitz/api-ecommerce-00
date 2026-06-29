<?php

declare(strict_types=1);

namespace App\Modules\Attribute\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Modules\Attribute\DTO\AttributeValueData;
use App\Modules\Attribute\Http\Requests\AttributeValueRequest;
use App\Modules\Attribute\Http\Resources\AttributeValueResource;
use App\Modules\Attribute\Services\AttributeValueService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class AttributeValueController extends BaseController
{
    public function __construct(
        private readonly AttributeValueService $attributeValueService
    ) {}

    public function index()
    {
        $values = $this->attributeValueService->getAllAttributeValues();

        return $this->sendSuccess(
            AttributeValueResource::collection($values),
            'Attribute values retrieved'
        );
    }

    public function store(AttributeValueRequest $request)
    {
        $user = $request->user();
        $shopId = $request->shop_id;
        if (! $this->attributeValueService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $data = AttributeValueData::fromRequest($request->validated());
        $value = $this->attributeValueService->createAttributeValue($data);

        return $this->sendSuccess(
            new AttributeValueResource($value),
            'Attribute value created',
            201
        );
    }

    public function show(int $id)
    {
        $value = $this->attributeValueService->getAttributeValueById($id);

        return $this->sendSuccess(
            new AttributeValueResource($value),
            'Attribute value detail'
        );
    }

    public function update(AttributeValueRequest $request, int $id)
    {
        $value = $this->attributeValueService->getAttributeValueById($id);
        $user = $request->user();
        $shopId = $request->shop_id ?? $value->shop_id;
        if (! $this->attributeValueService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $data = AttributeValueData::fromRequest($request->validated());
        $updated = $this->attributeValueService->updateAttributeValue($value, $data);

        return $this->sendSuccess(
            new AttributeValueResource($updated),
            'Attribute value updated'
        );
    }

    public function destroy(Request $request, int $id)
    {
        $value = $this->attributeValueService->getAttributeValueById($id);
        $user = $request->user();
        $shopId = $value->shop_id;
        if (! $this->attributeValueService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $this->attributeValueService->deleteAttributeValue($value);

        return $this->sendSuccess(null, 'Attribute value deleted');
    }
}
