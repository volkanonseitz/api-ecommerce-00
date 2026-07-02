<?php

declare(strict_types=1);

namespace App\Modules\StoreNotice\Actions;

use App\Models\StoreNotice;

final class MarkMultipleStoreNoticesAsReadAction
{
    public function execute(array $noticeIds, int $userId): void
    {
        foreach ($noticeIds as $noticeId) {
            $notice = StoreNotice::find($noticeId);
            if ($notice) {
                $notice->read_status()->syncWithoutDetaching([$userId => ['is_read' => true]]);
            }
        }
    }
}
