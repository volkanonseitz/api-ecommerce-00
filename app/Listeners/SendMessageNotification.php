<?php

namespace App\Listeners;

use App\Modules\Message\Events\MessageSent; // Event yang sudah direstrukturisasi
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendMessageNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        $message = $event->message;
        $conversation = $event->conversation;
        $sender = $event->sender;

        Log::info('Message sent notification processed: Message ID '.$message->id.' by '.$sender->name);

        // Di chawkbazaar-main, ini mungkin melibatkan broadcast ke channel khusus
        // Misalnya:
        // broadcast(new NewMessageBroadcast($message, $conversation))->toOthers();
        // Jika dibutuhkan, Anda perlu mengimplementasikan broadcasting event
    }
}
