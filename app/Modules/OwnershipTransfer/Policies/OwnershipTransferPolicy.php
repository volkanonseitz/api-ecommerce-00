<?php

declare(strict_types=1);

namespace App\Modules\OwnershipTransfer\Policies;

use App\Enums\Permission;
use App\Models\OwnershipTransfer;
use App\Models\User;

class OwnershipTransferPolicy
{
    /**
     * Determine if the user can view any transfer records.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $user->hasPermissionTo(Permission::STORE_OWNER->value);
    }

    /**
     * Determine if the user can view a specific transfer record.
     */
    public function view(User $user, OwnershipTransfer $transfer): bool
    {
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }

        // Store owner yang terkait (from atau to)
        return $user->id === $transfer->from || $user->id === $transfer->to;
    }

    /**
     * Determine if the user can create a transfer request.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::STORE_OWNER->value)
            || $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    /**
     * Determine if the user can update a transfer request (approve/reject).
     */
    public function update(User $user, OwnershipTransfer $transfer): bool
    {
        // Hanya super admin yang bisa approve/reject
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    /**
     * Determine if the user can delete a transfer request.
     */
    public function delete(User $user, OwnershipTransfer $transfer): bool
    {
        // Super admin bisa delete semua
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }

        // Store owner yang membuat request (from) bisa delete
        return $user->id === $transfer->from;
    }
}
