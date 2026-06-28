<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Attachment;
use App\Modules\Attachment\Actions\UploadAttachmentAction;
use App\Modules\Attachment\DTO\AttachmentData;
use App\Modules\Attachment\Http\Requests\AttachmentRequest;
use App\Modules\Attachment\Http\Resources\AttachmentResource;
use App\Modules\Attachment\Services\AttachmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttachmentController extends BaseController
{
    public function __construct(
        private readonly AttachmentService $queryService,
        private readonly UploadAttachmentAction $uploadAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Attachment::class);

        $perPage = (int) $request->input('limit', 15);
        $perPage = max(1, min($perPage, 100));

        $attachments = $this->queryService->getAll($perPage);

        return $this->sendPaginated(
            $attachments,
            AttachmentResource::collection($attachments->getCollection()),
            'Daftar attachment berhasil diambil.'
        );
    }

    public function store(AttachmentRequest $request): JsonResponse
    {
        $this->authorize('create', Attachment::class);

        $data = AttachmentData::fromRequest($request->validated());
        $results = $this->uploadAction->execute($data);

        return response()->json($results, 201);
    }

    public function show(int $id): JsonResponse
    {
        $attachment = $this->queryService->findOrFail($id);
        $this->authorize('view', $attachment);

        return $this->sendSuccess(
            new AttachmentResource($attachment),
            'Data attachment berhasil diambil.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        // Tidak ada update untuk attachment
        return response()->json(false);
    }

    public function destroy(int $id): JsonResponse
    {
        $attachment = $this->queryService->findOrFail($id);
        $this->authorize('delete', $attachment);

        $this->queryService->delete($attachment);

        return $this->sendSuccess(null, 'Attachment deleted successfully.');
    }
}
