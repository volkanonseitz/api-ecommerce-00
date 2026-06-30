<?php

declare(strict_types=1);

namespace App\Modules\Feedback\Policies;

use App\Models\Feedback;
use App\Models\User;

class FeedbackPolicy
{
    public function viewAny(User $user): bool
    {
        // Admin bisa lihat semua, user biasa hanya lihat milik sendiri (di service)
        return true;
    }

    public function view(User $user, Feedback $feedback): bool
    {
        return $user->id === $feedback->user_id || $user->hasPermissionTo('super_admin');
    }

    public function create(User $user): bool
    {
        return $user !== null;
    }

    public function update(User $user, Feedback $feedback): bool
    {
        return $user->id === $feedback->user_id || $user->hasPermissionTo('super_admin');
    }

    public function delete(User $user, Feedback $feedback): bool
    {
        return $this->update($user, $feedback);
    }
}
