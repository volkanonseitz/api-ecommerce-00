<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

class ConversationService
{
    public function getUserConversations()
    {
        $user = Auth::user();

        return Conversation::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereIn('shop_id', $user->shops->pluck('id'))
                ->orWhere('shop_id', $user->shop_id);
        })->with(['user.profile', 'shop'])->orderBy('updated_at', 'desc');
    }

    public function findConversation(int $id)
    {
        $user = Auth::user();
        $conversation = Conversation::with(['shop', 'user.profile'])->findOrFail($id);
        $allowed = ($user->shop_id === $conversation->shop_id) ||
                   (in_array($conversation->shop_id, $user->shops->pluck('id')->toArray())) ||
                   ($user->id === $conversation->user_id);
        abort_unless($allowed, 404, 'Unauthorized');

        return $conversation;
    }

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
