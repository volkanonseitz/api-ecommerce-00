<?php

namespace App\Http\Controllers;

use App\Services\AttachmentService;
use App\Http\Requests\AttachmentRequest;
use App\Http\Resources\AttachmentResource;
use App\DTO\AttachmentData;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    public function __construct(private AttachmentService $attachmentService) {}

    public function index(Request $request)
    {
        $attachments = $this->attachmentService->getAll();
        return AttachmentResource::collection($attachments);
    }

    public function store(AttachmentRequest $request)
    {
        $data = AttachmentData::fromRequest($request->validated());
        $results = $this->attachmentService->upload($data);
        return response()->json($results);
    }

    public function show($id)
    {
        $attachment = $this->attachmentService->find($id);
        return new AttachmentResource($attachment);
    }

    public function update(Request $request, $id)
    {
        // Tidak ada update untuk attachment, return false sesuai original
        return response()->json(false);
    }

    public function destroy($id)
    {
        $this->attachmentService->delete($id);
        return response()->json(['message' => 'Attachment deleted successfully']);
    }
}