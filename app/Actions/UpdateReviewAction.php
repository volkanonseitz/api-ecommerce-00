<?php

namespace App\Actions;

use App\Models\Review;
use App\Modules\Review\DTO\ReviewData;

class UpdateReviewAction
{
    public function execute(Review $review, ReviewData $data): Review
    {
        $review->update($data->toArray());

        return $review->fresh();
    }
}
