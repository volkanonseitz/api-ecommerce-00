<?php

namespace App\Services;

use App\Models\Feedback;
use App\DTO\FeedbackData;
use Illuminate\Database\Eloquent\Model;

class FeedbackService
{
    /**
     * Resolve model class from string type (e.g. "Review" -> App\Models\Review)
     */
    private function resolveModelClass(string $type): string
    {
        $map = [
            'Review'   => \App\Models\Review::class,
            'Question' => \App\Models\Question::class,
        ];

        return $map[$type] ?? 'App\\Models\\' . $type;
    }

    /**
     * Find the target model by type and id
     */
    public function findTargetModel(string $type, int $id): Model
    {
        $class = $this->resolveModelClass($type);
        return $class::findOrFail($id);
    }

    /**
     * Get existing feedback by user and target model
     */
    public function getExistingFeedback(Model $target, int $userId): ?Feedback
    {
        return $target->feedbacks()->where('user_id', $userId)->first();
    }

    /**
     * Create new feedback
     */
    public function createFeedback(Model $target, FeedbackData $data): Feedback
    {
        return $target->feedbacks()->create($data->toArray());
    }

    /**
     * Update existing feedback (toggle positive/negative)
     */
    public function toggleFeedback(Feedback $feedback, bool $positive, bool $negative): Feedback
    {
        $feedback->update([
            'positive' => $positive ? true : null,
            'negative' => $negative ? true : null,
        ]);
        return $feedback->fresh();
    }

    /**
     * Delete feedback
     */
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