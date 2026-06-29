<?php

declare(strict_types=1);

namespace App\Modules\Conversation\Services;

use App\Enums\Permission;
use App\Models\Conversation;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;

class ConversationService
{
    /**
     * Get conversations for the given user.
     *
     * @return Builder<Conversation>
     */
    public function getUserConversations(Authenticatable $user): Builder
    {
        // Ambil ID toko yang dimiliki (store owner) atau tempat staff bekerja
        $shopIds = [];
        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            $shopIds = $user->shops()->pluck('id')->toArray();
        } elseif ($user->hasPermissionTo(Permission::STAFF->value) && $user->shop_id) {
            $shopIds = [$user->shop_id];
        }

        return Conversation::where(function ($query) use ($user, $shopIds) {
            $query->where('user_id', $user->id);
            if (! empty($shopIds)) {
                $query->orWhereIn('shop_id', $shopIds);
            }
        })
            ->with(['user', 'shop']) // cukup load user dan shop
            ->orderBy('updated_at', 'desc');
    }

    /**
     * Find a conversation and ensure the user has access.
     */
    public function findConversation(int $id, Authenticatable $user): Conversation
    {
        return Conversation::with(['user', 'shop'])->findOrFail($id);
    }

    /**
     * Create a new conversation between a user and a shop.
     *
     * @throws \Exception
     */
    public function createConversation(int $userId, int $shopId): Conversation
    {
        // Cek apakah sudah ada
        $existing = Conversation::where('user_id', $userId)
            ->where('shop_id', $shopId)
            ->first();
        if ($existing) {
            return $existing;
        }

        return Conversation::create([
            'user_id' => $userId,
            'shop_id' => $shopId,
        ]);
    }
}
