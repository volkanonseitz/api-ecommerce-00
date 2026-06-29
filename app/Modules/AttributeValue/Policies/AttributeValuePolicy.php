<?php

declare(strict_types=1);

namespace App\Modules\Attribute\Policies;

use App\Enums\Permission;
use App\Models\AttributeValue;
use App\Models\User;

class AttributeValuePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AttributeValue $attributeValue): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $user->hasPermissionTo(Permission::STORE_OWNER->value);
    }

    public function update(User $user, AttributeValue $attributeValue): bool
    {
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }
        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $attributeValue->shop_id && $attributeValue->shop->owner_id === $user->id;
        }

        return false;
    }

    public function delete(User $user, AttributeValue $attributeValue): bool
    {
        return $this->update($user, $attributeValue);
    }
}
