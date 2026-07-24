<?php

declare(strict_types=1);

namespace App\Modules\Type\Policies;

use App\Enums\Permission;
use App\Models\Type;
use App\Models\User;

class TypePolicy
{
    public function viewAny(?User $user): bool
    {
        return true; // Types are generally public
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Type $type): bool
    {
        return true; // Types are generally public
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
    public function update(User $user, Type $type): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Type $type): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }
}
