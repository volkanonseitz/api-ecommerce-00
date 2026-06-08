<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Settings;
use App\DTO\QuestionData;
use App\Actions\CreateQuestionAction;
use App\Actions\UpdateQuestionAction;
use App\Events\QuestionAnswered;
use App\Enums\Permission;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class QuestionService
{
    public function __construct(
        private CreateQuestionAction $createQuestion,
        private UpdateQuestionAction $updateQuestion,
    ) {}

    /**
     * Cek permission untuk update question (menjawab)
     */
    public function hasPermission(?Authenticatable $user, ?int $shopId): bool
    {
        if (!$user) return false;
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) return true;
        if (!$shopId) return false;

        $shop = Shop::find($shopId);
        if (!$shop) return false;

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $shop->owner_id === $user->id;
        }
        if ($user->hasPermissionTo(Permission::STAFF->value)) {
            return $shop->staffs->contains($user->id);
        }
        return false;
    }

    public function getQuestionsQuery(Request $request): Builder
    {
        $query = Question::query();

        $productId = $request->input('product_id');
        if ($productId) {
            $query->where('product_id', $productId)->whereNotNull('answer');
            return $query;
        }

        $answerParam = $request->input('answer');
        if ($answerParam === 'null') {
            return $query;
        }

        return $query->whereNotNull('answer');
    }

    public function findOrFail(int $id): Question
    {
        return Question::findOrFail($id);
    }

    public function getUserQuestions(int $userId, int $perPage = 15)
    {
        return Question::where('user_id', $userId)->with('product')->paginate($perPage);
    }

    public function countUserQuestionsForProduct(int $userId, int $productId, int $shopId): int
    {
        return Question::where('product_id', $productId)
            ->where('user_id', $userId)
            ->where('shop_id', $shopId)
            ->count();
    }

    public function getMaximumQuestionLimit(): int
    {
        $settings = Settings::getData();
        return $settings->options['maximumQuestionLimit'] ?? 5;
    }

    public function createQuestion(QuestionData $data): Question
    {
        return $this->createQuestion->execute($data);
    }

    public function updateQuestion(Question $question, QuestionData $data): Question
    {
        $updated = $this->updateQuestion->execute($question, $data);
        if (!empty($updated->answer)) {
            event(new QuestionAnswered($updated));
        }
        return $updated;
    }

    public function deleteQuestion(Question $question): void
    {
        $question->delete();
    }
}