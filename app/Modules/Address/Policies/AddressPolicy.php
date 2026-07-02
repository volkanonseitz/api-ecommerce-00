<?php

declare(strict_types=1);

namespace App\Modules\Address\Policies;

use App\Enums\Permission;
use App\Models\Address;
use App\Models\User;

final class AddressPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view their own addresses
    }

    public function create(User $user): bool
    {
        return true; // All authenticated users can create addresses
    }

    public function view(User $user, Address $address): bool
    {
        return $user->id === $address->customer_id
            || $user->hasRole(Permission::SUPER_ADMIN->value);
    }

    public function update(User $user, Address $address): bool
    {
        return $this->view($user, $address);
    }

    public function delete(User $user, Address $address): bool
    {
        return $this->view($user, $address);
    }
}
