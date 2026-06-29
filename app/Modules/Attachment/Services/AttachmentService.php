<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Services;

use App\Models\Attachment;
use App\Modules\Attachment\Actions\CreateAttachmentAction;
use App\Modules\Attachment\DTO\AttachmentData;

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

    public function find(int $id): Attachment
    {
        return Attachment::findOrFail($id);
    }

    public function delete(int $id): void
    {
        $attachment = Attachment::findOrFail($id);
        $attachment->delete();
    }
}
