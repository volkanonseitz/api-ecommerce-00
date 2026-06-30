<?php

declare(strict_types=1);

namespace App\Modules\Language\Policies;

use App\Enums\Permission;
use App\Models\Language;
use App\Models\User;

class LanguagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Language $language): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function update(User $user, Language $language): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Language $language): bool
    {
        return $this->create($user);
    }
}
