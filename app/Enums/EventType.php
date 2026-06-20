<?php

namespace App\Enums;

enum FlashSaleType: string
{
    case ORDER_CANCELLED = 'cancelOrder';
    case ORDER_CREATED = 'createOrder';
    case ORDER_DELIVERED = 'deliverOrder';
    case ORDER_PAYMENT = 'paymentOrder';
    case ORDER_PAYMENT_FAILED = 'paymentFailedOrder';
    case ORDER_PAYMENT_SUCCESS = 'paymentSuccessOrder';
    case ORDER_REFUND = 'refundOrder';
    case ORDER_STATUS_CHANGED = 'statusChangeOrder';
    case ORDER_UPDATED = 'updateOrder';
    case QUESTION_ANSWERED = 'answerQuestion';
    case QUESTION_CREATED = 'createQuestion';
    case REVIEW_CREATED = 'createReview';

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
        return match ($this) {
            self::ORDER_CANCELLED => 'cancelOrder',
            self::ORDER_CREATED => 'createOrder',
            self::ORDER_DELIVERED => 'deliverOrder',
            self::ORDER_PAYMENT => 'paymentOrder',
            self::ORDER_PAYMENT_FAILED => 'paymentFailedOrder',
            self::ORDER_PAYMENT_SUCCESS => 'paymentSuccessOrder',
            self::ORDER_REFUND => 'refundOrder',
            self::ORDER_STATUS_CHANGED => 'statusChangeOrder',
            self::ORDER_UPDATED => 'updateOrder',
            self::QUESTION_ANSWERED => 'answerQuestion',
            self::QUESTION_CREATED => 'createQuestion',
            self::REVIEW_CREATED => 'createReview',
        };
    }
}
