<?php

declare(strict_types=1);

namespace App\Modules\Question\Actions;

use App\Models\Question;

final class DeleteQuestionAction
{
    public function execute(Question $question): void
    {
        $question->delete();
    }
}
