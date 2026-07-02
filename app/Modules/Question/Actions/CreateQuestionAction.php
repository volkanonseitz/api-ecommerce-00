<?php

declare(strict_types=1);

namespace App\Modules\Question\Actions;

use App\Models\Question;
use App\Modules\Question\DTO\QuestionData;

final class CreateQuestionAction
{
    public function execute(QuestionData $data): Question
    {
        return Question::create($data->toArray());
    }
}
