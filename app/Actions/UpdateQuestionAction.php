<?php

namespace App\Actions;

use App\DTO\QuestionData;
use App\Models\Question;

class UpdateQuestionAction
{
    public function execute(Question $question, QuestionData $data): Question
    {
        $question->update($data->toArray());

        return $question->fresh();
    }
}
