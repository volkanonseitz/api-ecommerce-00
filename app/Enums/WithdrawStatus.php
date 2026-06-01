<?php

namespace App\Enums;

enum WithdrawStatus: string
{
    case APPROVED = 'approved';
    case PENDING = 'pending';
    case ON_HOLD = 'on_hold';
    case REJECTED = 'rejected';
    case PROCESSING = 'processing';

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
            self::APPROVED => 'Approved',
            self::PENDING => 'Pending',
            self::ON_HOLD => 'On Hold',
            self::REJECTED => 'Rejected',
            self::PROCESSING => 'Processing',
        };
    }
}