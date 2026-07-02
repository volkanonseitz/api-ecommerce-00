<?php

declare(strict_types=1);

namespace App\Modules\Coupon\Policies;

use App\Enums\Permission;
use App\Models\Coupon;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class CouponPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view coupons
    }

    public function view(User $user, Coupon $coupon): bool
    {
        // Super admin can view any coupon
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }

        // Store owner can view coupons associated with their shops
        if ($user->hasPermissionTo(Permission::STORE_OWNER->value) && $coupon->shop_id) {
            $shop = Shop::find($coupon->shop_id);

            return $shop && $shop->owner_id === $user->id;
        }

        // Staff can view coupons associated with their assigned shop
        if ($user->hasPermissionTo(Permission::STAFF->value) && $coupon->shop_id && $user->shop_id === $coupon->shop_id) {
            return true;
        }

        // Customer can view approved coupons
        return $coupon->is_approve;
    }

    public function create(User $user, ?int $shopId = null): bool
    {
        // Super admin can create any coupon
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }

        // Store owner can create coupons for their shops
        if ($user->hasPermissionTo(Permission::STORE_OWNER->value) && $shopId) {
            $shop = Shop::find($shopId);

            return $shop && $shop->owner_id === $user->id;
        }

        return false;
    }

    public function update(User $user, Coupon $coupon): bool
    {
        // Super admin can update any coupon
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }

        // Store owner can update coupons associated with their shops
        if ($user->hasPermissionTo(Permission::STORE_OWNER->value) && $coupon->shop_id) {
            $shop = Shop::find($coupon->shop_id);

            return $shop && $shop->owner_id === $user->id;
        }

        return false;
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        // Super admin can delete any coupon
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }

        // Store owner can delete coupons associated with their shops
        if ($user->hasPermissionTo(Permission::STORE_OWNER->value) && $coupon->shop_id) {
            $shop = Shop::find($coupon->shop_id);

            return $shop && $shop->owner_id === $user->id;
        }

        return false;
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
