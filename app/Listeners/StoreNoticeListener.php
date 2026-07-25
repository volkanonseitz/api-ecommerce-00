<?php

namespace App\Listeners;

use App\Events\StoreNoticeEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class StoreNoticeListener implements ShouldQueue
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
        Log::info('Store Notice Event handled: '.$event->action, [
            'store_notice_id' => $event->storeNotice->id,
            'actor_id' => $event->actor->id,
            'action' => $event->action,
        ]);

        // Logika tambahan bisa ditambahkan di sini, misalnya:
        // - Mengirim notifikasi broadcast ke frontend
        // - Memperbarui cache terkait Store Notice
    }
}
