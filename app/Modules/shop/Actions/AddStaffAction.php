<?php

declare(strict_types=1);

namespace App\Modules\Shop\Actions;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use App\Modules\Shop\DTO\StaffData;
use Illuminate\Support\Facades\Hash;

final class AddStaffAction
{
    public function execute(StaffData $data): User
    {
        $staff = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'shop_id' => $data->shop_id,
        ]);

        $staff->givePermissionTo(Permission::CUSTOMER->value, Permission::STAFF->value);
        $staff->assignRole(Role::STAFF->value);

        return $staff;
    }
}
