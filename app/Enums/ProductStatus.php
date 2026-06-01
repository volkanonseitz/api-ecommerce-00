<?php

namespace App\Enums;

enum ProductStatus: string
{
    case UNDER_REVIEW = 'under_review';
	case APPROVED = 'approved';
	case REJECTED = 'rejected';
	case PUBLISH = 'publish';
	case UNPUBLISH = 'unpublish';
	case DRAFT = 'draft';

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
            self::UNDER_REVIEW  => 'Under Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::PUBLISH => 'Publish',
            self::UNPUBLISH => 'Unpublish',
            self::DRAFT => 'Draft',
        };
    }
}