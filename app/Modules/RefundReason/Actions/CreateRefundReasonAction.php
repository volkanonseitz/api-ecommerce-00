<?php

declare(strict_types=1);

namespace App\Modules\RefundReason\Actions;

use App\Models\RefundReason;
use App\Modules\RefundReason\DTO\RefundReasonData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class CreateRefundReasonAction
{
    public function execute(RefundReasonData $data): RefundReason
    {
        $attributes = array_filter([
            'name' => $data->name,
            'slug' => $data->slug ?? Str::slug($data->name),
            'language' => $data->language,
        ], fn ($v) => ! is_null($v));

        $reason = RefundReason::create($attributes);

        Cache::forget("refund_reasons_{$reason->language}_*"); // Invalidate cache

        return $reason;
    }
}
