<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Address;
use App\Models\User;

class AddressPolicy
{
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
