<?php

namespace App\Services;

use App\Models\RefundReason;
use App\DTO\RefundReasonData;
use Illuminate\Support\Str;

class RefundReasonService
{
    public function getRefundReasons(string $language, int $perPage = 15)
    {
        return RefundReason::where('language', $language)->paginate($perPage);
    }

    public function find($params, string $language): RefundReason
    {
        if (is_numeric($params)) {
            return RefundReason::where('id', $params)->firstOrFail();
        }
        return RefundReason::where('slug', $params)->where('language', $language)->firstOrFail();
    }

    public function create(RefundReasonData $data): RefundReason
    {
        $attributes = array_filter([
            'name' => $data->name,
            'slug' => $data->slug ?? Str::slug($data->name),
            'language' => $data->language,
        ], fn($v) => !is_null($v));

        return RefundReason::create($attributes);
    }

    public function update(RefundReason $reason, RefundReasonData $data): RefundReason
    {
        $attributes = array_filter([
            'name' => $data->name,
            'slug' => ($data->slug && $data->slug !== $reason->slug) ? $data->slug : ($data->name ? Str::slug($data->name) : null),
            'language' => $data->language,
        ], fn($v) => !is_null($v));

        $reason->update($attributes);
        return $reason->fresh();
    }

    public function delete(RefundReason $reason): void
    {
        $reason->delete();
    }
}