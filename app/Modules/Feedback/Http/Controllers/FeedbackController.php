<?php

declare(strict_types=1);

namespace App\Modules\Feedback\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Feedback;
use App\Modules\Feedback\DTO\FeedbackData;
use App\Modules\Feedback\Http\Requests\FeedbackCreateRequest;
use App\Modules\Feedback\Http\Resources\FeedbackResource;
use App\Modules\Feedback\Services\FeedbackService;

class FeedbackController extends BaseController
{
    public function __construct(private FeedbackService $feedbackService) {}

    public function index()
    {
        $this->authorize('viewAny', Feedback::class);
        $feedbacks = $this->feedbackService->getFeedbackWithUser();

        return FeedbackResource::collection($feedbacks);
    }

    public function store(FeedbackCreateRequest $request)
    {
        $this->authorize('create', Feedback::class);
        $userId = (int) $request->user()->id;
        $data = FeedbackData::fromRequest($request->validated(), $userId);

        $target = $this->feedbackService->findTargetModel($data->model_type, $data->model_id);
        $existing = $this->feedbackService->getExistingFeedback($target, $userId);

        if (! $existing) {
            $feedback = $this->feedbackService->createFeedback($target, $data);
        } else {
            $positive = $data->positive;
            $negative = $data->negative;

            if ($positive && $existing->negative === true) {
                $feedback = $this->feedbackService->toggleFeedback($existing, true, false);
            } elseif ($negative && $existing->positive === true) {
                $feedback = $this->feedbackService->toggleFeedback($existing, false, true);
            } else {
                $feedback = $existing;
            }
        }

        return new FeedbackResource($feedback);
    }

    public function show(int $id)
    {
        $feedback = $this->feedbackService->findFeedbackOrFail($id);
        $this->authorize('view', $feedback);

        return new FeedbackResource($feedback);
    }

    public function update()
    {
        return response()->json('update');
    }

    public function destroy(int $id)
    {
        $feedback = $this->feedbackService->findFeedbackOrFail($id);
        $this->authorize('delete', $feedback);
        $this->feedbackService->deleteFeedback($id);

        return $this->sendSuccess(null, 'Feedback deleted successfully');
    }
}
