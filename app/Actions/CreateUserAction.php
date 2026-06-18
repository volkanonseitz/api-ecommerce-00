<?php

namespace App\Actions;

use App\DTO\UserData;
use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    public function execute(UserData $data): User
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => Hash::make($data->password),
                'shop_id' => $data->shop_id,
            ]);

            $user->givePermissionTo(Permission::CUSTOMER->value);
            $user->assignRole(Role::CUSTOMER->value);

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
        });
    }
}
