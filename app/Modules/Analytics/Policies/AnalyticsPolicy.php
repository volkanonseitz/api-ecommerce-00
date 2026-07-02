<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Policies;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class AnalyticsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any analytics data.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value) ||
               $user->hasPermissionTo(Permission::STORE_OWNER->value) ||
               $user->hasPermissionTo(Permission::STAFF->value);
    }
}
