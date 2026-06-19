<?php

namespace App\Actions;

use App\DTO\ReviewData;
use App\Models\Review;

class CreateReviewAction
{
    public function execute(ReviewData $data): Review
    {
        return Review::create($data->toArray());
    }
}
