<?php

declare(strict_types=1);

namespace App\Modules\Resource\Policies;

use App\Enums\Permission;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResourcePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Resources are generally public or accessible to all authenticated users
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Resource $resource): bool
    {
        return true; // Resources are generally public or accessible to all authenticated users
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
    public function update(User $user, Resource $resource): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Resource $resource): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }
}
