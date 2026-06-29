<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Attachment;
use App\Modules\Attachment\DTO\AttachmentData;
use App\Modules\Attachment\Http\Requests\AttachmentRequest;
use App\Modules\Attachment\Http\Resources\AttachmentResource;
use App\Modules\Attachment\Services\AttachmentService;
use Illuminate\Http\Request;

class AttachmentController extends BaseController
{
    public function __construct(private AttachmentService $attachmentService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Attachment::class);
        $attachments = $this->attachmentService->getAll();

        return AttachmentResource::collection($attachments);
    }

    public function store(AttachmentRequest $request)
    {
        $this->authorize('create', Attachment::class);
        $data = AttachmentData::fromRequest($request->validated());
        $results = $this->attachmentService->upload($data);

        return response()->json($results);
    }

    public function show(int $id)
    {
        $attachment = $this->attachmentService->find($id);
        $this->authorize('view', $attachment);

        return new AttachmentResource($attachment);
    }

    public function update(Request $request, int $id)
    {
        return response()->json(false);
    }

    public function destroy(int $id)
    {
        $this->authorize('delete', Attachment::class);
        $this->attachmentService->delete($id);

        return $this->sendSuccess(null, 'Attachment deleted successfully');
    }
}
