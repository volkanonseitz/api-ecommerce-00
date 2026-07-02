<?php

declare(strict_types=1);

namespace App\Modules\Question\Actions;

use App\Events\QuestionAnswered;
use App\Models\Question;
use App\Modules\Question\DTO\QuestionData;

final class UpdateQuestionAction
{
    public function execute(Question $question, QuestionData $data): Question
    {
        $question->update($data->toArray());

        if (! empty($question->answer)) {
            event(new QuestionAnswered($question));
        }

        return $question->fresh();
    }
}
