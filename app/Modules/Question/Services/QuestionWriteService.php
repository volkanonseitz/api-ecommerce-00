<?php

declare(strict_types=1);

namespace App\Modules\Question\Services;

use App\Models\Question;
use App\Modules\Question\Actions\CreateQuestionAction;
use App\Modules\Question\Actions\DeleteQuestionAction;
use App\Modules\Question\Actions\UpdateQuestionAction;
use App\Modules\Question\DTO\QuestionData;

final class QuestionWriteService
{
    public function __construct(
        private readonly CreateQuestionAction $createQuestion,
        private readonly UpdateQuestionAction $updateQuestion,
        private readonly DeleteQuestionAction $deleteQuestion,
    ) {}

    public function createQuestion(QuestionData $data): Question
    {
        return $this->createQuestion->execute($data);
    }

    public function updateQuestion(Question $question, QuestionData $data): Question
    {
        return $this->updateQuestion->execute($question, $data);
    }

    public function deleteQuestion(Question $question): void
    {
        $this->deleteQuestion->execute($question);
    }
}
