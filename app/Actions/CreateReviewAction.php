<?php

namespace App\Actions;

use App\Models\Review;
use App\Modules\Review\DTO\ReviewData;

class CreateReviewAction
{
    public function execute(ReviewData $data): Review
    {
        return Review::create($data->toArray());
    }
}
