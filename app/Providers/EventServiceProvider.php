<?php

namespace App\Providers;

use App\Events\DigitalProductUpdateEvent;
use App\Events\OrderCreated;
use App\Events\OrderProcessed;
use App\Events\OrderReceived;
use App\Events\OwnershipTransferStatusControl;
use App\Events\PaymentMethods;
use App\Events\ProductReviewApproved;
use App\Events\ProductReviewRejected;
use App\Listeners\DigitalProductNotifyLogsListener;
use App\Listeners\ProductInventoryDecrement;
use App\Listeners\ProductReviewApprovedListener;
use App\Listeners\ProductReviewRejectedListener;
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

        DigitalProductUpdateEvent::class => [
            DigitalProductNotifyLogsListener::class,
        ],

        ProductReviewApproved::class => [
            ProductReviewApprovedListener::class,
        ],
        ProductReviewRejected::class => [
            ProductReviewRejectedListener::class,
        ],
        OwnershipTransferStatusControl::class => [
        ],
        PaymentMethods::class => [
        ],
    ];

    public function boot(): void
    {
        //
    }
}
