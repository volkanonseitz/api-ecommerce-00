<?php

declare(strict_types=1);

namespace App\Modules\Withdraw\Policies;

use App\Enums\Permission;
use App\Models\User;
use App\Models\Withdraw;
use Illuminate\Auth\Access\HandlesAuthorization;

class WithdrawPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Super admin can view all withdraws. Store owner can view their shop's withdraws.
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $user->hasPermissionTo(Permission::STORE_OWNER->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Withdraw $withdraw): bool
    {
        // Super admin can view any withdraw. Store owner can view withdraws from their shops.
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || ($user->hasPermissionTo(Permission::STORE_OWNER->value) && $user->shops->contains($withdraw->shop_id));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only store owners can create withdraw requests.
        return $user->hasPermissionTo(Permission::STORE_OWNER->value);
    }

    /**
     * Determine whether the user can approve/reject a withdraw.
     */
    public function approve(User $user): bool
    {
        // Only super admin can approve/reject withdraws.
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Withdraw $withdraw): bool
    {
        // Only super admin can delete withdraws.
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }
}
