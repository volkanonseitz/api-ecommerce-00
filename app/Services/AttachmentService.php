<?php

namespace App\Services;

use App\Models\Attachment;
use App\DTO\AttachmentData;
use App\Actions\CreateAttachmentAction;

class AttachmentService
{
    public function __construct(private CreateAttachmentAction $createAction) {}

    public function upload(AttachmentData $data): array
    {
        return $this->createAction->execute($data);
    }

    public function getAll()
    {
        return Attachment::paginate();
    }

    public function find($id): Attachment
    {
        return Attachment::findOrFail($id);
    }

    public function delete($id): void
    {
        $attachment = Attachment::findOrFail($id);
        $attachment->delete();
    }
}