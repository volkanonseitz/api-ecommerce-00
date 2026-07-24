<?php

declare(strict_types=1);

namespace App\Modules\FlashSaleRequest\Policies;

use App\Enums\Permission;
use App\Models\FlashSaleRequest;
use App\Models\User;

class FlashSaleRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $user->hasPermissionTo(Permission::STORE_OWNER->value)
            || $user->hasPermissionTo(Permission::STAFF->value);
    }

    public function view(User $user, FlashSaleRequest $request): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $user->hasPermissionTo(Permission::STORE_OWNER->value)
            || $user->hasPermissionTo(Permission::STAFF->value);
    }

    public function update(User $user, FlashSaleRequest $request): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, FlashSaleRequest $request): bool
    {
        return $this->create($user);
    }

    public function approve(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function disapprove(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }
}
