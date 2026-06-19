<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\OrderProcessed;
use App\Events\OrderReceived;
use App\Listeners\ProductInventoryDecrement;
use App\Listeners\SendOrderCreationNotification;
use App\Listeners\SendOrderReceivedNotification;
use App\Listeners\StoredOrderNotifyLogsListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OrderCreated::class => [
            SendOrderCreationNotification::class,
            StoredOrderNotifyLogsListener::class,
        ],

        OrderProcessed::class => [
            ProductInventoryDecrement::class,
        ],

        OrderReceived::class => [
            SendOrderReceivedNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
