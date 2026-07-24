<?php

declare(strict_types=1);

namespace App\Modules\Category\Policies;

use App\Enums\Permission;
use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Category $category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function update(User $user, Category $category): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->create($user);
    }
}
