<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Attachment;
use App\Modules\Attachment\DTO\AttachmentData;
use App\Modules\Attachment\Http\Requests\AttachmentRequest;
use App\Modules\Attachment\Http\Resources\AttachmentResource;
use App\Modules\Attachment\Services\AttachmentQueryService;
use App\Modules\Attachment\Services\AttachmentWriteService;
use Illuminate\Http\Request;

class AttachmentController extends BaseController
{
    public function __construct(
        private readonly AttachmentQueryService $attachmentQueryService,
        private readonly AttachmentWriteService $attachmentWriteService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Attachment::class);
        $attachments = $this->attachmentQueryService->getAll();

        return AttachmentResource::collection($attachments);
    }

    public function store(AttachmentRequest $request)
    {
        $this->authorize('create', Attachment::class);
        $data = AttachmentData::fromRequest($request->validated());
        $results = $this->attachmentWriteService->upload($data);

        return response()->json($results);
    }

    public function show(int $id)
    {
        $attachment = $this->attachmentQueryService->find($id);
        $this->authorize('view', $attachment);

        return new AttachmentResource($attachment);
    }

    public function destroy(int $id)
    {
        $attachment = $this->attachmentQueryService->find($id);
        $this->authorize('delete', $attachment);
        $this->attachmentWriteService->delete($id);

        return $this->sendSuccess(null, 'Attachment deleted successfully');
    }
}
