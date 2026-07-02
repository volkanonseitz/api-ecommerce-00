<?php

declare(strict_types=1);

namespace App\Modules\RefundReason\Actions;

use App\Models\RefundReason;
use Illuminate\Support\Facades\Cache;

final class DeleteRefundReasonAction
{
    public function execute(RefundReason $reason): void
    {
        $language = $reason->language;
        $reason->delete();

        Cache::forget("refund_reasons_{$language}_*"); // Invalidate cache
    }
}
