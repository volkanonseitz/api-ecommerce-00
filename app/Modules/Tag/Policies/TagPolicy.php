<?php

declare(strict_types=1);

namespace App\Modules\Tag\Policies;

use App\Enums\Permission;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; // Tags are generally public to view
    }

    public function view(User $user, Tag $tag): bool
    {
        return true; // Tags are generally public to view
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }
}