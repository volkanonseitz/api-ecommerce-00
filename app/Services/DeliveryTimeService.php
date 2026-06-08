<?php

namespace App\Services;

use App\Models\DeliveryTime;
use App\DTO\DeliveryTimeData;
use Illuminate\Support\Str;

class DeliveryTimeService
{
    public function getAll(string $language)
    {
        return DeliveryTime::where('language', $language)->get();
    }

    public function find($params, string $language): DeliveryTime
    {
        if (is_numeric($params)) {
            return DeliveryTime::where('id', $params)->where('language', $language)->firstOrFail();
        }
        return DeliveryTime::where('slug', $params)->where('language', $language)->firstOrFail();
    }

    public function create(DeliveryTimeData $data): DeliveryTime
    {
        $attributes = array_filter([
            'title' => $data->title,
            'slug' => $data->slug ?? Str::slug($data->title),
            'language' => $data->language,
            'description' => $data->description,
            'icon' => $data->icon,
        ], fn($v) => !is_null($v));

        return DeliveryTime::create($attributes);
    }

    public function update(DeliveryTime $deliveryTime, DeliveryTimeData $data): DeliveryTime
    {
        $attributes = array_filter([
            'title' => $data->title,
            'slug' => ($data->slug && $data->slug !== $deliveryTime->slug) ? $data->slug : ($data->title ? Str::slug($data->title) : null),
            'language' => $data->language,
            'description' => $data->description,
            'icon' => $data->icon,
        ], fn($v) => !is_null($v));

        $deliveryTime->update($attributes);
        return $deliveryTime->fresh();
    }

    public function delete(DeliveryTime $deliveryTime): void
    {
        $deliveryTime->delete();
    }
}