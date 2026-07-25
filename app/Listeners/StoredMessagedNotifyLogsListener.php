<?php

namespace App\Listeners;

use App\Enums\EventType; // Asumsi EventType enum ada
use App\Models\NotifyLogs; // Asumsi model NotifyLogs ada
use App\Modules\Message\Events\MessageSent; // Event yang sudah direstrukturisasi
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class StoredMessagedNotifyLogsListener implements ShouldQueue
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
        try {
            NotifyLogs::create([
                'user_id' => $event->sender->id,
                'notify_type' => EventType::MESSAGE_SENT, // Asumsi ini adalah value yang benar
                'notify_text' => 'Message sent by '.$event->sender->name.' in conversation '.$event->conversation->id.'.',
                'related_id' => $event->message->id,
                'is_read' => false,
            ]);
            Log::info('Notify log created for MessageSent event: Message ID '.$event->message->id);
        } catch (Throwable $e) {
            Log::error('Failed to create notify log for MessageSent event: '.$e->getMessage(), [
                'event' => $event->message->toArray(),
                'sender' => $event->sender->toArray(),
            ]);
        }
    }
}
