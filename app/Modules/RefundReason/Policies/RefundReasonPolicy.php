<?php

declare(strict_types=1);

namespace App\Modules\RefundReason\Policies;

use App\Enums\Permission;
use App\Models\RefundReason;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefundReasonPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Refund reasons are generally public or accessible to all authenticated users
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, RefundReason $refundReason): bool
    {
        return true; // Refund reasons are generally public or accessible to all authenticated users
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RefundReason $refundReason): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RefundReason $refundReason): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }
}
