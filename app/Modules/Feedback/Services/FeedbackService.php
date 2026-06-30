<?php

declare(strict_types=1);

namespace App\Modules\Feedback\Services;

use App\Models\Feedback;
use App\Models\Question;
use App\Models\Review;
use App\Modules\Feedback\Actions\CreateFeedbackAction;
use App\Modules\Feedback\Actions\ToggleFeedbackAction;
use App\Modules\Feedback\DTO\FeedbackData;
use Illuminate\Database\Eloquent\Model;

class FeedbackService
{
    public function __construct(
        private CreateFeedbackAction $createAction,
        private ToggleFeedbackAction $toggleAction,
    ) {}

    /**
     * @return class-string
     */
    private function resolveModelClass(string $type): string
    {
        $map = [
            'Review' => Review::class,
            'Question' => Question::class,
        ];

        return $map[$type] ?? 'App\\Models\\'.$type;
    }

    public function findTargetModel(string $type, int $id): Model
    {
        $class = $this->resolveModelClass($type);

        return $class::findOrFail($id);
    }

    public function getExistingFeedback(Model $target, int $userId): ?Feedback
    {
        return $target->feedbacks()->where('user_id', $userId)->first();
    }

    public function createFeedback(Model $target, FeedbackData $data): Feedback
    {
        return $this->createAction->execute($target, $data);
    }

    public function toggleFeedback(Feedback $feedback, bool $positive, bool $negative): Feedback
    {
        return $this->toggleAction->execute($feedback, $positive, $negative);
    }

    public function deleteFeedback(int $id): void
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();
    }

    public function getFeedbackWithUser(int $perPage = 15)
    {
        return Feedback::with('user')->paginate($perPage);
    }

    public function findFeedbackOrFail(int $id): Feedback
    {
        return Feedback::findOrFail($id);
    }
}
