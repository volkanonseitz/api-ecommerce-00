<?php

namespace App\Providers;

use App\Events\Maintenance;
use App\Events\UserRegistered;
use App\Listeners\CheckAndSetDefaultCard;
use App\Listeners\CommissionRateUpdateListener;
use App\Listeners\ConfirmStockConsumption;
use App\Listeners\DigitalProductNotifyLogsListener;
use App\Listeners\EventWebhookHandler;
use App\Listeners\FlashSaleProductProcess;
use App\Listeners\MaintenanceNotification;
use App\Listeners\MessageParticipantNotification;
use App\Listeners\OwnershipTransferredListener;
use App\Listeners\OwnershipTransferStatusControlListener;
use App\Listeners\ProductInventoryDecrement;
use App\Listeners\ProductReviewApprovedListener;
use App\Listeners\ProductReviewRejectedListener;
use App\Listeners\ReleaseReservedStock;
use App\Listeners\SendMessageNotification;
use App\Listeners\SendOrderCancelledNotification;
use App\Listeners\SendOrderCreationNotification;
use App\Listeners\SendOrderDeliveredNotification;
use App\Listeners\SendOrderReceivedNotification;
use App\Listeners\SendOrderShippedNotification;
use App\Listeners\SendOrderStatusChangedNotification;
use App\Listeners\SendPasswordResetSuccessNotification;
use App\Listeners\SendPaymentFailedNotification;
use App\Listeners\SendPaymentSuccessfulNotification;
use App\Listeners\SendProductLowStockNotification;
use App\Listeners\SendQuestionAnsweredNotification;
use App\Listeners\SendRefundRequestedNotification;
use App\Listeners\SendRefundUpdateNotification;
use App\Listeners\SendReviewNotification;
use App\Listeners\SendUserRegisteredNotification;
use App\Listeners\ShopMaintenanceListener;
use App\Listeners\StoredMessagedNotifyLogsListener;
use App\Listeners\StoredOrderNotifyLogsListener;
use App\Listeners\StoredStoreNoticeNotifyLogsListener;
use App\Listeners\StoreNoticeListener;
use App\Modules\Download\Events\DigitalProductUpdateEvent;
use App\Modules\FlashSale\Events\FlashSaleProcessed;
use App\Modules\Message\Events\MessageSent;
use App\Modules\Message\Events\QuestionAnswered;
use App\Modules\NotifyLogs\Events\StoreNoticeEvent;
use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Events\OrderDelivered;
use App\Modules\Order\Events\OrderProcessed;
use App\Modules\Order\Events\OrderReceived;
use App\Modules\Order\Events\OrderShipped;
use App\Modules\Order\Events\OrderStatusChanged;
use App\Modules\Payment\Events\PaymentFailed;
use App\Modules\Payment\Events\PaymentSuccess;
use App\Modules\PaymentMethod\Events\PaymentMethods;
use App\Modules\Refund\Events\RefundRequested;
use App\Modules\Refund\Events\RefundUpdate;
use App\Modules\Review\Events\ProductReviewApproved;
use App\Modules\Review\Events\ProductReviewRejected;
use App\Modules\Review\Events\ReviewCreated;
use App\Modules\Shop\Events\CommissionRateUpdateEvent;
use App\Modules\Shop\Events\OwnershipTransferStatusControl;
use App\Modules\Shop\Events\ProcessOwnershipTransition;
use App\Modules\Shop\Events\ProductLowStock;
use App\Modules\Shop\Events\ShopMaintenance;
use Illuminate\Auth\Events\PasswordReset;
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
            EventWebhookHandler::class,
        ],
        OrderShipped::class => [
            SendOrderShippedNotification::class,
            EventWebhookHandler::class,
        ],
        OrderProcessed::class => [
            ProductInventoryDecrement::class,
        ],
        OrderReceived::class => [
            SendOrderReceivedNotification::class,
        ],
        OrderCancelled::class => [
            SendOrderCancelledNotification::class,
            ReleaseReservedStock::class,
        ],
        OrderDelivered::class => [
            SendOrderDeliveredNotification::class,
            ConfirmStockConsumption::class,
            EventWebhookHandler::class,
        ],
        OrderStatusChanged::class => [
            SendOrderStatusChangedNotification::class,
            EventWebhookHandler::class,
        ],

        FlashSaleProcessed::class => [
            FlashSaleProductProcess::class,
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
        RefundRequested::class => [
            SendRefundRequestedNotification::class,
        ],
        RefundUpdate::class => [
            SendRefundUpdateNotification::class,
        ],
        CommissionRateUpdateEvent::class => [
            CommissionRateUpdateListener::class,
        ],
        ShopMaintenance::class => [
            ShopMaintenanceListener::class,
        ],

        OwnershipTransferStatusControl::class => [
            OwnershipTransferStatusControlListener::class,
        ],
        ProcessOwnershipTransition::class => [
            OwnershipTransferredListener::class,
        ],
        PaymentFailed::class => [
            SendPaymentFailedNotification::class,
        ],
        PaymentSuccess::class => [
            SendPaymentSuccessfulNotification::class,
        ],
        QuestionAnswered::class => [
            SendQuestionAnsweredNotification::class,
        ],
        ReviewCreated::class => [
            SendReviewNotification::class,
        ],
        StoreNoticeEvent::class => [
            StoreNoticeListener::class,
            StoredStoreNoticeNotifyLogsListener::class,
        ],
        Maintenance::class => [
            MaintenanceNotification::class,
        ],
        PaymentMethods::class => [
            CheckAndSetDefaultCard::class,
        ],
        MessageSent::class => [
            MessageParticipantNotification::class,
            SendMessageNotification::class,
            StoredMessagedNotifyLogsListener::class,
        ],

        UserRegistered::class => [
            SendUserRegisteredNotification::class,
            EventWebhookHandler::class,
        ],
        PasswordReset::class => [
            SendPasswordResetSuccessNotification::class,
        ],
        ProductLowStock::class => [
            SendProductLowStockNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
