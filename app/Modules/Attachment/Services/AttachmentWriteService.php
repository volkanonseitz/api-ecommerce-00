<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Services;

use App\Modules\Attachment\Actions\CreateAttachmentAction;
use App\Modules\Attachment\DTO\AttachmentData;

final class AttachmentWriteService
{
    public function __construct(private readonly CreateAttachmentAction $createAction) {}

    public function upload(AttachmentData $data): array
    {
        return $this->createAction->execute($data);
    }
}
