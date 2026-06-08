<?php

namespace App\Http\Controllers;

use App\Services\QuestionService;
use App\Http\Requests\QuestionCreateRequest;
use App\Http\Requests\QuestionUpdateRequest;
use App\Http\Resources\QuestionResource;
use App\DTO\QuestionData;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class QuestionController extends Controller
{
    public function __construct(private QuestionService $questionService) {}

    /**
     * GET /questions
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 15;
        $questions = $this->questionService->getQuestionsQuery($request)->paginate($limit);
        return QuestionResource::collection($questions);
    }

    /**
     * POST /questions
     */
    public function store(QuestionCreateRequest $request)
    {
        $userId = $request->user()->id;
        $productId = $request->product_id;
        $shopId = $request->shop_id;

        $userQuestionCount = $this->questionService->countUserQuestionsForProduct($userId, $productId, $shopId);
        $maxLimit = $this->questionService->getMaximumQuestionLimit();

        if ($userQuestionCount >= $maxLimit) {
            throw new HttpException(400, config('notice.MAXIMUM_QUESTION_LIMIT_EXCEEDED'));
        }

        $data = QuestionData::fromRequest($request->validated(), $userId);
        $question = $this->questionService->createQuestion($data);
        return new QuestionResource($question);
    }

    /**
     * GET /questions/{id}
     */
    public function show($id)
    {
        $question = $this->questionService->findOrFail($id);
        return new QuestionResource($question);
    }

    /**
     * PUT /questions/{id}
     */
    public function update(QuestionUpdateRequest $request, $id)
    {
        $question = $this->questionService->findOrFail($id);
        $shopId = $request->shop_id ?? $question->shop_id;

        if (!$this->questionService->hasPermission($request->user(), $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $data = QuestionData::fromRequest($request->validated(), $question->user_id);
        $updated = $this->questionService->updateQuestion($question, $data);
        return new QuestionResource($updated);
    }

    /**
     * DELETE /questions/{id}
     */
    public function destroy($id)
    {
        $question = $this->questionService->findOrFail($id);
        $this->questionService->deleteQuestion($question);
        return response()->json(['message' => 'Question deleted successfully']);
    }

    /**
     * GET /my-questions
     */
    public function myQuestions(Request $request)
    {
        $limit = $request->limit ?? 15;
        $questions = $this->questionService->getUserQuestions($request->user()->id, $limit);
        return QuestionResource::collection($questions);
    }
}