<?php

declare(strict_types=1);

namespace App\Modules\Tax\Policies;

use App\Enums\Permission;
use App\Models\Tax;
use App\Models\User;

class TaxPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Taxes are generally public or accessible to all authenticated users
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Tax $tax): bool
    {
        return true; // Taxes are generally public or accessible to all authenticated users
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
    public function update(User $user, Tax $tax): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tax $tax): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }
}
