<?php

declare(strict_types=1);

namespace App\Modules\FlashSale\Policies;

use App\Enums\Permission;
use App\Models\FlashSale;
use App\Models\User;

class FlashSalePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FlashSale $flashSale): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function update(User $user, FlashSale $flashSale): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, FlashSale $flashSale): bool
    {
        return $this->create($user);
    }
}
