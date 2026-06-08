<?php

namespace App\Http\Controllers;

use App\Services\FeedbackService;
use App\Http\Requests\FeedbackCreateRequest;
use App\Http\Resources\FeedbackResource;
use App\DTO\FeedbackData;

class FeedbackController extends Controller
{
    public function __construct(private FeedbackService $feedbackService) {}

    /**
     * GET /feedbacks
     */
    public function index()
    {
        $feedbacks = $this->feedbackService->getFeedbackWithUser();
        return FeedbackResource::collection($feedbacks);
    }

    /**
     * POST /feedbacks
     */
    public function store(FeedbackCreateRequest $request)
    {
        $userId = $request->user()->id;
        $data = FeedbackData::fromRequest($request->validated(), $userId);

        $target = $this->feedbackService->findTargetModel($data->model_type, $data->model_id);
        $existing = $this->feedbackService->getExistingFeedback($target, $userId);

        if (!$existing) {
            // create new feedback
            $feedback = $this->feedbackService->createFeedback($target, $data);
        } else {
            // toggle: if positive requested and old was negative, flip
            $positive = $data->positive;
            $negative = $data->negative;

            if ($positive && $existing->negative === true) {
                $feedback = $this->feedbackService->toggleFeedback($existing, true, false);
            } elseif ($negative && $existing->positive === true) {
                $feedback = $this->feedbackService->toggleFeedback($existing, false, true);
            } else {
                // no change or already same, just return existing
                $feedback = $existing;
            }
        }

        return new FeedbackResource($feedback);
    }

    /**
     * GET /feedbacks/{id}
     */
    public function show($id)
    {
        $feedback = $this->feedbackService->findFeedbackOrFail($id);
        return new FeedbackResource($feedback);
    }

    /**
     * PUT /feedbacks/{id} (not implemented in original, return string)
     */
    public function update()
    {
        return 'update';
    }

    /**
     * DELETE /feedbacks/{id}
     */
    public function destroy($id)
    {
        $this->feedbackService->deleteFeedback($id);
        return response()->json(['message' => 'Feedback deleted successfully']);
    }
}