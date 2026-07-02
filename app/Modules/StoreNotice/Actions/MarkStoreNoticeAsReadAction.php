<?php

declare(strict_types=1);

namespace App\Modules\StoreNotice\Actions;

use App\Models\StoreNotice;

final class MarkStoreNoticeAsReadAction
{
    public function execute(StoreNotice $notice, int $userId): void
    {
        $notice->read_status()->syncWithoutDetaching([$userId => ['is_read' => true]]);
    }
}
