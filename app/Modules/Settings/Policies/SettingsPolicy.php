<?php

declare(strict_types=1);

namespace App\Modules\Settings\Policies;

use App\Enums\Permission;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SettingsPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; // Settings are generally public to view
    }

    public function view(User $user, Settings $settings): bool
    {
        return true; // Settings are generally public to view
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function update(User $user, Settings $settings): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function delete(User $user, Settings $settings): bool
    {
        return false; // Settings should not be deleted
    }
}