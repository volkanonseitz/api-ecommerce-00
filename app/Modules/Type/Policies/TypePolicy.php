<?php

declare(strict_types=1);

namespace App\Modules\Type\Policies;

use App\Enums\Permission;
use App\Models\Type;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TypePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function view(User $user, Type $type): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function update(User $user, Type $type): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function delete(User $user, Type $type): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function restore(User $user, Type $type): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function forceDelete(User $user, Type $type): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }
}