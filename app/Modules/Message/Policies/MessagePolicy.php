<?php

declare(strict_types=1);

namespace App\Modules\Message\Policies;

use App\Enums\Permission;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * Determine if the user can view messages in a conversation.
     */
    public function viewAny(User $user, Conversation $conversation): bool
    {
        // Customer yang terlibat
        if ($user->id === $conversation->user_id) {
            return true;
        }

        // Store owner yang memiliki toko
        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $user->shops()->where('id', $conversation->shop_id)->exists();
        }

        // Staff yang bekerja di toko tersebut
        if ($user->hasPermissionTo(Permission::STAFF->value)) {
            return $user->shop_id === $conversation->shop_id;
        }

        return false;
    }

    /**
     * Determine if the user can create a message.
     */
    public function create(User $user, Conversation $conversation): bool
    {
        return $this->viewAny($user, $conversation);
    }

    /**
     * Determine if the user can mark messages as seen.
     */
    public function markAsSeen(User $user, Conversation $conversation): bool
    {
        return $this->viewAny($user, $conversation);
    }
}
