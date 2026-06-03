<?php

namespace App\Actions;

use App\Models\User;
use App\Models\Profile;
use App\Models\Address;
use App\DTO\UserData;
use Illuminate\Support\Facades\Hash;
use App\Enums\Permission;
use App\Enums\Role;

class CreateUserAction
{
    public function execute(UserData $data): User
    {
        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'shop_id' => $data->shop_id,
        ]);

        // Default permissions
        $user->givePermissionTo(Permission::CUSTOMER->value);
        $user->assignRole(Role::CUSTOMER->value);

        // Jika ada permission tambahan (untuk store_owner)
        if ($data->permission) {
            $user->givePermissionTo($data->permission);
            if ($data->permission === Permission::STORE_OWNER->value) {
                $user->assignRole(Role::STORE_OWNER->value);
            }
        }

        if ($data->profile) {
            $user->profile()->create($data->profile);
        }

        if ($data->address) {
            foreach ($data->address as $address) {
                $user->address()->create($address);
            }
        }

        return $user;
    }
}