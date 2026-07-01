<?php

namespace App\Actions;

use App\Modules\Review\DTO\ReviewData;
use App\Models\Review;

class CreateReviewAction
{
    public function execute(ReviewData $data): Review
    {
        return Review::create($data->toArray());
    }
}
