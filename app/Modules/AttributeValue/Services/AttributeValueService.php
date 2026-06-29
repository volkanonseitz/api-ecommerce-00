<?php

declare(strict_types=1);

namespace App\Modules\Attribute\Services;

use App\Enums\Permission;
use App\Models\AttributeValue;
use App\Models\Shop;
use App\Modules\Attribute\Actions\CreateAttributeValueAction;
use App\Modules\Attribute\Actions\UpdateAttributeValueAction;
use App\Modules\Attribute\DTO\AttributeValueData;
use Illuminate\Contracts\Auth\Authenticatable;

class AttributeValueService
{
    public function __construct(
        private CreateAttributeValueAction $createAction,
        private UpdateAttributeValueAction $updateAction,
    ) {}

    public function hasPermission(?Authenticatable $user, ?int $shopId): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }
        if (! $shopId) {
            return false;
        }

        $shop = Shop::find($shopId);
        if (! $shop || ! $shop->is_active) {
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
        return $this->createAction->execute($data);
    }

    public function updateAttributeValue(AttributeValue $attributeValue, AttributeValueData $data): AttributeValue
    {
        return $this->updateAction->execute($attributeValue, $data);
    }

    public function deleteAttributeValue(AttributeValue $attributeValue): void
    {
        $attributeValue->delete();
    }
}
