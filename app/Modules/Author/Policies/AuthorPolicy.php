<?php

declare(strict_types=1);

namespace App\Modules\Author\Policies;

use App\Enums\Permission;
use App\Models\Author;
use App\Models\User;

class AuthorPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Author $author): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $user->hasPermissionTo(Permission::STORE_OWNER->value);
    }

    public function update(User $user, Author $author): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Author $author): bool
    {
        return $this->create($user);
    }
}
