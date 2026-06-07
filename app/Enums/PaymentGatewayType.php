<?php

namespace App\Enums;

enum PaymentGatewayType: string
{
    case STRIPE = 'STRIPE';
    case PAYPAL = 'PAYPAL';
    case CASH_ON_DELIVERY = 'CASH_ON_DELIVERY';
    case CASH = 'CASH';
    case FULL_WALLET_PAYMENT = 'FULL_WALLET_PAYMENT';

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
            self::STRIPE => 'Stripe',
            self::PAYPAL => 'PayPal',
            self::CASH_ON_DELIVERY => 'Cash on Delivery',
            self::CASH => 'Cash',
            self::FULL_WALLET_PAYMENT => 'Full Wallet Payment',
        };
    }
}