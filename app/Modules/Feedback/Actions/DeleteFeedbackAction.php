<?php

declare(strict_types=1);

namespace App\Modules\Feedback\Actions;

use App\Models\Feedback;

class DeleteFeedbackAction
{
    public function execute(Feedback $feedback): void
    {
        $feedback->delete();
    }
}
