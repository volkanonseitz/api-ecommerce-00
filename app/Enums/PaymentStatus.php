<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING                = 'payment-pending';
    case PROCESSING             = 'payment-processing';
    case SUCCESS                = 'payment-success';
    case FAILED                 = 'payment-failed';
    case REVERSAL               = 'payment-reversal';
    case REFUNDED               = 'payment-refunded';
    case CASH_ON_DELIVERY       = 'payment-cash-on-delivery';
    case CASH                   = 'payment-cash';
    case WALLET                 = 'payment-wallet';
    case AWAITING_FOR_APPROVAL  = 'payment-awaiting-for-approval';

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
            self::PENDING                => 'Payment Pending',
            self::PROCESSING             => 'Payment Processing',
            self::SUCCESS                => 'Payment Success',
            self::FAILED                 => 'Payment Failed',
            self::REVERSAL               => 'Payment Reversal',
            self::REFUNDED               => 'Payment Refunded',
            self::CASH_ON_DELIVERY       => 'Cash on Delivery',
            self::CASH                   => 'Cash',
            self::WALLET                 => 'Wallet',
            self::AWAITING_FOR_APPROVAL  => 'Awaiting for Approval',
        };
    }
}