<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Modules\Order\Events\OrderShipped;
use App\Notifications\OrderShippedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendOrderShippedNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(OrderShipped $event): void
    {
        $event->order->customer->notify(new OrderShippedNotification($event->order));
        if ($event->order->shop && $event->order->shop->owner) {
            $event->order->shop->owner->notify(new OrderShippedNotification($event->order));
        }
    }
}
