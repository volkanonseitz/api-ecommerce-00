<?php

namespace App\Providers;

use App\Events\DigitalProductUpdateEvent;
use App\Events\OrderCreated;
use App\Events\OrderProcessed;
use App\Events\OrderReceived;
use App\Events\OwnershipTransferStatusControl;
use App\Events\PaymentMethods;
use App\Events\ProcessOwnershipTransition;
use App\Events\ProductReviewApproved;
use App\Events\ProductReviewRejected;
use App\Events\ShopMaintenance;
use App\Listeners\DigitalProductNotifyLogsListener;
use App\Listeners\OwnershipTransferredListener;
use App\Listeners\OwnershipTransferStatusControlListener; // tambahkan jika ada
use App\Listeners\ProductInventoryDecrement;
use App\Listeners\ProductReviewApprovedListener;
use App\Listeners\ProductReviewRejectedListener;
use App\Listeners\SendOrderCreationNotification;
use App\Listeners\SendOrderReceivedNotification;
use App\Listeners\ShopMaintenanceListener;
use App\Listeners\StoredOrderNotifyLogsListener;
// jika class ini ada di Listeners, bukan Notifications
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
        // Hapus duplikasi OwnershipTransferStatusControl, gabungkan listener-nya
        OwnershipTransferStatusControl::class => [
            OwnershipTransferStatusControlListener::class,
            // tambahkan listener lain jika ada
        ],
        PaymentMethods::class => [
            // tambahkan listener jika ada, atau biarkan kosong
        ],
        ShopMaintenance::class => [
            ShopMaintenanceListener::class,
        ],
        ProcessOwnershipTransition::class => [
            OwnershipTransferredListener::class, // ganti dengan listener yang benar
        ],
    ];

    public function boot(): void
    {
        //
    }
}
