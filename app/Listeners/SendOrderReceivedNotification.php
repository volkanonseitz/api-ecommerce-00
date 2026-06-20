<?php

namespace App\Listeners;

use App\Events\OrderReceived;
use App\Notifications\NewOrderReceived;
use App\Services\NotificationRecipientService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderReceivedNotification implements ShouldQueue
{
    protected NotificationRecipientService $recipientService;

    public function __construct(NotificationRecipientService $recipientService)
    {
        $this->recipientService = $recipientService;
    }

    public function handle(OrderReceived $event)
    {
        $order = $event->order;
        $language = $order->language ?? config('shop.default_language', 'id');

        $recipients = $this->recipientService->getWhichUserWillGetEmail('order_created', $language);

        if ($recipients['vendor'] && $order->shop && $order->shop->owner) {
            $vendor = $order->shop->owner;
            $vendor->notify(new NewOrderReceived($order));
        }

        // Jika perlu juga notifikasi ke admin, tambahkan di sini.
    }
}
