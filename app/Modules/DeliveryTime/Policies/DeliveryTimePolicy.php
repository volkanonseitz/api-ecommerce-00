<?php

declare(strict_types=1);

namespace App\Modules\DeliveryTime\Policies;

use App\Enums\Permission;
use App\Models\DeliveryTime;
use App\Models\User;

class DeliveryTimePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DeliveryTime $deliveryTime): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function update(User $user, DeliveryTime $deliveryTime): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, DeliveryTime $deliveryTime): bool
    {
        return $this->create($user);
    }
}
