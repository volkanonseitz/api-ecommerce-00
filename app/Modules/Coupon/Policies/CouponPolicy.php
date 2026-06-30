<?php

declare(strict_types=1);

namespace App\Modules\Coupon\Policies;

use App\Enums\Permission;
use App\Models\Coupon;
use App\Models\User;

class CouponPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Coupon $coupon): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $user->hasPermissionTo(Permission::STORE_OWNER->value);
    }

    public function update(User $user, Coupon $coupon): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        return $this->create($user);
    }

    public function approve(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }
}
