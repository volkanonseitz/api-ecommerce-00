<?php

namespace App\Services;

use App\Models\AttributeValue;
use App\DTO\AttributeValueData;
use App\Enums\Permission;
use Illuminate\Contracts\Auth\Authenticatable;

class AttributeValueService
{
    public function hasPermission(?Authenticatable $user, ?int $shopId): bool
    {
        if (!$user) return false;
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) return true;
        if (!$shopId) return false;

        $shop = Shop::find($shopId);
        if (!$shop || !$shop->is_active) {
            throw new \Exception(config('notice.SHOP_NOT_APPROVED'));
        }
        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $shop->owner_id === $user->id;
        }
        return false;
    }

    public function getAllAttributeValues()
    {
        return AttributeValue::with('attribute')->get();
    }

    public function getAttributeValueById(int $id): AttributeValue
    {
        return AttributeValue::with('attribute')->findOrFail($id);
    }

    public function createAttributeValue(AttributeValueData $data): AttributeValue
    {
        $attributes = array_filter([
            'value' => $data->value,
            'meta' => $data->meta,
            'price' => $data->price,
            'shop_id' => $data->shop_id,
            'attribute_id' => $data->attribute_id,
            'language' => $data->language,
        ], fn($v) => !is_null($v));
        return AttributeValue::create($attributes);
    }

    public function updateAttributeValue(AttributeValue $value, AttributeValueData $data): AttributeValue
    {
        $updateData = array_filter([
            'value' => $data->value,
            'meta' => $data->meta,
            'price' => $data->price,
            'shop_id' => $data->shop_id,
            'attribute_id' => $data->attribute_id,
            'language' => $data->language,
        ], fn($v) => !is_null($v));
        $value->update($updateData);
        return $value->fresh();
    }

    public function deleteAttributeValue(AttributeValue $value): void
    {
        $value->delete();
    }
}