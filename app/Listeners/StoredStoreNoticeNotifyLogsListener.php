<?php

namespace App\Listeners;

use App\Enums\EventType; // Asumsi EventType enum ada
use App\Events\StoreNoticeEvent;
use App\Models\NotifyLogs; // Asumsi model NotifyLogs ada
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class StoredStoreNoticeNotifyLogsListener implements ShouldQueue
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
    public function handle(StoreNoticeEvent $event): void
    {
        try {
            NotifyLogs::create([
                'user_id' => $event->actor->id,
                'notify_type' => EventType::STORE_NOTICE, // Asumsi ini adalah value yang benar
                'notify_text' => "Store Notice '".$event->storeNotice->title."' was ".$event->action.' by '.$event->actor->name.'.',
                'related_id' => $event->storeNotice->id,
                'is_read' => false,
            ]);
            Log::info('Notify log created for Store Notice event: '.$event->storeNotice->title);
        } catch (\Exception $e) {
            Log::error('Failed to create notify log for Store Notice event: '.$e->getMessage(), [
                'event' => $event->storeNotice->toArray(),
                'actor' => $event->actor->toArray(),
            ]);
        }
    }
}
