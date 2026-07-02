<?php

namespace App\Modules\Question\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Question;
use App\Modules\Question\DTO\QuestionData;
use App\Modules\Question\Http\Requests\QuestionCreateRequest;
use App\Modules\Question\Http\Requests\QuestionUpdateRequest;
use App\Modules\Question\Http\Resources\QuestionResource;
use App\Modules\Question\Services\QuestionQueryService;
use App\Modules\Question\Services\QuestionWriteService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class QuestionController extends BaseController
{
    public function __construct(
        private readonly QuestionQueryService $questionQueryService,
        private readonly QuestionWriteService $questionWriteService,
    ) {}

    /**
     * GET /questions
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 15;
        $questions = $this->questionQueryService->getQuestionsQuery($request)->paginate($limit);

        return QuestionResource::collection($questions);
    }

    /**
     * POST /questions
     */
    public function store(QuestionCreateRequest $request)
    {
        $this->authorize('create', Question::class);

        $userId = $request->user()->id;
        $productId = $request->product_id;
        $shopId = $request->shop_id;

        $userQuestionCount = $this->questionQueryService->countUserQuestionsForProduct($userId, $productId, $shopId);
        $maxLimit = $this->questionQueryService->getMaximumQuestionLimit();

        if ($userQuestionCount >= $maxLimit) {
            throw new HttpException(400, config('notice.MAXIMUM_QUESTION_LIMIT_EXCEEDED'));
        }

        $data = QuestionData::fromRequest($request->validated(), $userId);
        $question = $this->questionWriteService->createQuestion($data);

        return new QuestionResource($question);
    }

    /**
     * GET /questions/{id}
     */
    public function show($id)
    {
        $question = $this->questionQueryService->findOrFail($id);
        $this->authorize('view', $question);

        return new QuestionResource($question);
    }

    /**
     * PUT /questions/{id}
     */
    public function update(QuestionUpdateRequest $request, $id)
    {
        $question = $this->questionQueryService->findOrFail($id);
        $this->authorize('update', $question);

        $data = QuestionData::fromRequest($request->validated(), $question->user_id);
        $updated = $this->questionWriteService->updateQuestion($question, $data);

        return new QuestionResource($updated);
    }

    /**
     * DELETE /questions/{id}
     */
    public function destroy($id)
    {
        $question = $this->questionQueryService->findOrFail($id);
        $this->authorize('delete', $question);

        $this->questionWriteService->deleteQuestion($question);

        return response()->json(['message' => 'Question deleted successfully']);
    }

    /**
     * GET /my-questions
     */
    public function myQuestions(Request $request)
    {
        $limit = $request->limit ?? 15;
        $questions = $this->questionQueryService->getUserQuestions($request->user()->id, $limit);

        return QuestionResource::collection($questions);
    }
}
