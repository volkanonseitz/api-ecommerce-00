<?php

declare(strict_types=1);

namespace App\Modules\User\Actions;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class ToggleAdminPrivilegeAction
{
    public function execute(User $target): bool
    {
        return DB::transaction(function () use ($target): bool {
            $isCurrentlyAdmin = $target->hasPermissionTo(Permission::SUPER_ADMIN->value);

            if ($isCurrentlyAdmin) {
                $target->revokePermissionTo(Permission::SUPER_ADMIN->value);
                $target->removeRole(Role::SUPER_ADMIN->value);
            } else {
                $target->givePermissionTo(Permission::SUPER_ADMIN->value);
                $target->assignRole(Role::SUPER_ADMIN->value);
            }

            Cache::forget('cached_admin');

            return ! $isCurrentlyAdmin; // true = sekarang admin, false = admin dicabut
        });
    }
}
