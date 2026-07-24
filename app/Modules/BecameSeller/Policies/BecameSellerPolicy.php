<?php

declare(strict_types=1);

namespace App\Modules\BecameSeller\Policies;

use App\Enums\Permission;
use App\Models\User;

class BecameSellerPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function update(User $user): bool
    {
        return $this->create($user);
    }
}
