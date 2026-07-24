<?php

declare(strict_types=1);

namespace App\Modules\NotifyLogs\Policies;

use App\Enums\Permission;
use App\Models\NotifyLogs;
use App\Models\User;

class NotifyLogsPolicy
{
    /**
     * Determine if the user can view any notifications.
     */
    public function viewAny(?User $user): bool
    {
        return $user !== null;
    }

    /**
     * Determine if the user can view a specific notification.
     */
    public function view(User $user, NotifyLogs $log): bool
    {
        // User hanya bisa melihat notifikasi yang ditujukan untuk mereka
        // atau super admin
        if ($user->id === $log->receiver) {
            return true;
        }

        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    /**
     * Determine if the user can mark a notification as read.
     */
    public function markAsRead(User $user, NotifyLogs $log): bool
    {
        return $user->id === $log->receiver;
    }

    /**
     * Determine if the user can mark all notifications as read.
     */
    public function markAllAsRead(User $user): bool
    {
        return true; // user bisa menandai semua notifikasi mereka sebagai read
    }

    /**
     * Determine if the user can delete a notification.
     */
    public function delete(User $user, NotifyLogs $log): bool
    {
        // Hanya super admin yang bisa menghapus notifikasi
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }
}
