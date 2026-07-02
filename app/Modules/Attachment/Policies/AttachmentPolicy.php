<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Policies;

use App\Enums\Permission;
use App\Models\Attachment;
use App\Models\User;

final class AttachmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $user->hasPermissionTo(Permission::STORE_OWNER->value);
    }

    public function view(User $user, Attachment $attachment): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $user->hasPermissionTo(Permission::STORE_OWNER->value);
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return $this->create($user);
    }
}
