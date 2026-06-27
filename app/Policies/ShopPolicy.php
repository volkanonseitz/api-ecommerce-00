<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShopPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Shop $shop): bool
    {
        return $user->hasPermissionTo(Permission::STORE_OWNER->value) && $shop->owner_id === $user->id
            || $user->hasPermissionTo(Permission::STAFF->value) && $shop->staffs->contains($user->id);
    }

    public function update(User $user, Shop $shop): bool
    {
        return $user->hasPermissionTo(Permission::STORE_OWNER->value) && $shop->owner_id === $user->id;
    }

    public function delete(User $user, Shop $shop): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }
}
