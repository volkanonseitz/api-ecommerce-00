<?php

namespace App\Actions;

use App\DTO\QuestionData;
use App\Models\Question;

class CreateQuestionAction
{
    public function execute(QuestionData $data): Question
    {
        return Question::create($data->toArray());
    }
}
