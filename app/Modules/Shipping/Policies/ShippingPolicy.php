<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Policies;

use App\Enums\Permission;
use App\Models\Shipping;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShippingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Shipping options are generally public or accessible to all authenticated users
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Shipping $shipping): bool
    {
        return true; // Shipping options are generally public or accessible to all authenticated users
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
    public function update(User $user, Shipping $shipping): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Shipping $shipping): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }
}
