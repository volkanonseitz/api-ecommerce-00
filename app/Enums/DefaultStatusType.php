<?php

namespace App\Enums;

enum DefaultStatusType: string
{
    case PROCESSING = 'processing';
    case APPROVED = 'approved';
    case PENDING = 'pending';
    case REJECTED = 'rejected';

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
            self::PROCESSING => 'Processing',
            self::APPROVED => 'Approved',
            self::PENDING => 'Pending',
            self::REJECTED => 'Rejected',
        };
    }
}