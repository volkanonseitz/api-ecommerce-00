<?php

namespace App\Actions;

use App\Models\Review;
use App\DTO\ReviewData;

class CreateReviewAction
{
    public function execute(ReviewData $data): Review
    {
        return Review::create($data->toArray());
    }
}