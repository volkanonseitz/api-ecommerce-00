<?php

namespace App\Actions;

use App\Models\Question;
use App\Modules\Question\DTO\QuestionData;

class CreateQuestionAction
{
    public function execute(QuestionData $data): Question
    {
        return Question::create($data->toArray());
    }
}
