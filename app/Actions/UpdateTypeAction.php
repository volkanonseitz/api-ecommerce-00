<?php

namespace App\Actions;

use App\Models\Type;
use App\DTO\TypeData;
use Illuminate\Support\Str;

class UpdateTypeAction
{
    public function execute(Type $type, TypeData $data): Type
    {
        $attributes = array_filter([
            'name' => $data->name,
            'slug' => ($data->slug && $data->slug !== $type->slug) ? $data->slug : ($data->name ? Str::slug($data->name) : null),
            'icon' => $data->icon,
            'settings' => $data->settings,
            'promotional_sliders' => $data->promotional_sliders,
            'images' => $data->images,
            'language' => $data->language,
        ], fn($v) => !is_null($v));

        $type->update($attributes);

        if (!is_null($data->banners)) {
            $type->banners()->delete();
            $type->banners()->createMany($data->banners);
        }

        return $type->fresh('banners');
    }
}