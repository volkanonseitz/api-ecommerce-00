<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Actions;

use App\Models\Attachment;

final class DeleteAttachmentAction
{
    public function execute(Attachment $attachment): void
    {
        $attachment->delete();
    }
}
