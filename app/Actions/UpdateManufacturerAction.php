<?php

namespace App\Actions;

use App\Models\Manufacturer;
use App\DTO\ManufacturerData;
use Illuminate\Support\Str;

class UpdateManufacturerAction
{
    public function execute(Manufacturer $manufacturer, ManufacturerData $data): Manufacturer
    {
        $attributes = array_filter([
            'name' => $data->name,
            'slug' => ($data->slug && $data->slug !== $manufacturer->slug) ? $data->slug : ($data->name ? Str::slug($data->name) : null),
            'description' => $data->description,
            'type_id' => $data->type_id,
            'shop_id' => $data->shop_id,
            'image' => $data->image,
            'cover_image' => $data->cover_image,
            'is_approved' => $data->is_approved,
            'language' => $data->language,
            'website' => $data->website,
            'socials' => $data->socials,
        ], fn($v) => !is_null($v));

        $manufacturer->update($attributes);
        return $manufacturer->fresh('type');
    }
}