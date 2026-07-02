<?php

declare(strict_types=1);

namespace App\Modules\StoreNotice\Actions;

use App\Models\StoreNotice;

final class DeleteStoreNoticeAction
{
    public function execute(StoreNotice $storeNotice): void
    {
        $storeNotice->forceDelete();
    }
}
