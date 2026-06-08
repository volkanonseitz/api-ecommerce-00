<?php

namespace App\Actions;

use App\Models\Question;
use App\DTO\QuestionData;

class UpdateQuestionAction
{
    public function execute(Question $question, QuestionData $data): Question
    {
        $question->update($data->toArray());
        return $question->fresh();
    }
}