<?php

namespace App\Actions;

use App\Models\StoreNotice;
use App\DTO\StoreNoticeData;

class CreateStoreNoticeAction
{
    public function execute(StoreNoticeData $data): StoreNotice
    {
        $attributes = array_filter([
            'priority' => $data->priority,
            'notice' => $data->notice,
            'description' => $data->description,
            'effective_from' => $data->effective_from,
            'expired_at' => $data->expired_at,
            'type' => $data->type,
        ], fn($v) => !is_null($v));

        return StoreNotice::create($attributes);
    }
}