<?php

declare(strict_types=1);

namespace App\Modules\User\Policies;

use App\Enums\Permission;
use App\Models\User;

/**
 * Pusat aturan otorisasi seputar entitas User.
 * Dipakai di FormRequest::authorize() DAN di Controller (defense in depth),
 * sehingga aturan "siapa boleh apa" hanya didefinisikan SATU KALI di sini.
 */
class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function view(User $actor, User $target): bool
    {
        return $actor->id === $target->id
            || $actor->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->id === $target->id
            || $actor->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function delete(User $actor, User $target): bool
    {
        return $actor->id !== $target->id
            && $actor->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function toggleActive(User $actor, User $target): bool
    {
        return $actor->id !== $target->id
            && $actor->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function toggleAdmin(User $actor, User $target): bool
    {
        return $actor->id !== $target->id
            && $actor->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function viewShopAssignment(User $actor, User $target): bool
    {
        return $actor->id === $target->id
            || $actor->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $actor->hasPermissionTo(Permission::STORE_OWNER->value);
    }
}
