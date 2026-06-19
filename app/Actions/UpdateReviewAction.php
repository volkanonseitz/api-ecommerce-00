<?php

namespace App\Actions;

use App\DTO\ReviewData;
use App\Models\Review;

class UpdateReviewAction
{
    public function execute(Review $review, ReviewData $data): Review
    {
        $review->update($data->toArray());

        return $review->fresh();
    }
}
