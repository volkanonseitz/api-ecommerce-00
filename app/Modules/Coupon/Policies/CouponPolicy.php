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
        return true; // semua user bisa lihat daftar coupon
    }

    public function view(User $user, Coupon $coupon): bool
    {
        return true;
    }

    public function create(User $user, ?int $shopId = null): bool
    {
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value) && $shopId) {
            return $user->shops()->where('id', $shopId)->exists();
        }

        if ($user->hasPermissionTo(Permission::STAFF->value) && $shopId) {
            return $user->shop_id === $shopId;
        }

        return false;
    }

    public function update(User $user, Coupon $coupon): bool
    {
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $user->shops()->where('id', $coupon->shop_id)->exists();
        }

        if ($user->hasPermissionTo(Permission::STAFF->value)) {
            return $user->shop_id === $coupon->shop_id;
        }

        return false;
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        return $this->update($user, $coupon);
    }

    public function approve(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function disapprove(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }
}
