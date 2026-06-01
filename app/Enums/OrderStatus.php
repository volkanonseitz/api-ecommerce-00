<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING              = 'order-pending';
    case PROCESSING           = 'order-processing';
    case COMPLETED            = 'order-completed';
    case CANCELLED            = 'order-cancelled';
    case REFUNDED             = 'order-refunded';
    case FAILED               = 'order-failed';
    case AT_LOCAL_FACILITY    = 'order-at-local-facility';
    case OUT_FOR_DELIVERY     = 'order-out-for-delivery';

    /**
     * Get all values for database enum
     */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get label for display
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING              => 'Order Pending',
            self::PROCESSING           => 'Order Processing',
            self::COMPLETED            => 'Order Completed',
            self::CANCELLED            => 'Order Cancelled',
            self::REFUNDED             => 'Order Refunded',
            self::FAILED               => 'Order Failed',
            self::AT_LOCAL_FACILITY    => 'Order at Local Facility',
            self::OUT_FOR_DELIVERY     => 'Order Out for Delivery',
        };
    }
}