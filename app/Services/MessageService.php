<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Participant;
use App\DTO\MessageData;
use App\Actions\CreateMessageAction;
use App\Events\MessageSent;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MessageService
{
    public function __construct(private CreateMessageAction $createMessage) {}

    /**
     * Cek apakah user memiliki akses ke conversation
     */
    public function hasAccess(Authenticatable $user, Conversation $conversation): bool
    {
        // Jika user adalah customer yang memulai percakapan
        if ($user->id === $conversation->user_id) return true;

        // Jika user adalah pemilik shop atau staff dari shop tersebut
        $shopIds = $user->shops->pluck('id')->toArray();
        if (in_array($conversation->shop_id, $shopIds)) return true;

        // Jika user adalah staff yang memiliki shop_id sama (kasus user punya managed_shop)
        if ($user->shop_id && $user->shop_id === $conversation->shop_id) return true;

        return false;
    }

    /**
     * Ambil messages dengan pagination untuk conversation
     */
    public function getMessages(Conversation $conversation, int $perPage = 15): Builder
    {
        return $conversation->messages()
            ->with(['conversation.shop', 'conversation.user.profile'])
            ->orderBy('id', 'desc');
    }

    /**
     * Tandai pesan sudah dibaca (update participant)
     */
    public function markAsSeen(Conversation $conversation, Authenticatable $user): int
    {
        $updated = 0;

        // Jika user adalah customer
        $participant = Participant::where('conversation_id', $conversation->id)
            ->whereNull('last_read')
            ->where('user_id', $user->id)
            ->where('type', 'user')
            ->first();
        if ($participant) {
            $participant->last_read = Carbon::now();
            $participant->save();
            $updated++;
        }

        // Jika user adalah shop owner atau staff
        $shopIds = $user->shops->pluck('id')->toArray();
        if (in_array($conversation->shop_id, $shopIds) || $user->shop_id === $conversation->shop_id) {
            $participant = Participant::where('conversation_id', $conversation->id)
                ->whereNull('last_read')
                ->where('shop_id', $conversation->shop_id)
                ->where('type', 'shop')
                ->first();
            if ($participant) {
                $participant->last_read = Carbon::now();
                $participant->save();
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Simpan pesan baru
     */
    public function storeMessage(Conversation $conversation, MessageData $data, Authenticatable $user): Message
    {
        // Tentukan tipe penerima (shop atau user)
        $type = '';
        if ($user->id === $conversation->user_id) {
            $type = 'shop'; // customer mengirim ke shop
        } elseif (in_array($conversation->shop_id, $user->shops->pluck('id')->toArray()) || $user->shop_id === $conversation->shop_id) {
            $type = 'user'; // shop mengirim ke customer
        } else {
            throw new \Exception(config('notice.NOT_AUTHORIZED'));
        }

        $message = $this->createMessage->execute($data);
        $conversation->update(['updated_at' => now()]);
        event(new MessageSent($message, $conversation, $type, $user));
        return $message;
    }

    /**
     * Get conversation by id dengan akses check
     */
    public function getConversationForUser(int $conversationId, Authenticatable $user): Conversation
    {
        $conversation = Conversation::where('id', $conversationId)
            ->where(function($q) use ($user) {
                $q->where('user_id', $user->id);
                $q->orWhereIn('shop_id', $user->shops->pluck('id'));
                $q->orWhere('shop_id', $user->shop_id);
            })
            ->firstOrFail();
        return $conversation;
    }
}