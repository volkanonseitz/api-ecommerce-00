<?php

namespace App\Actions;

use App\Models\Type;
use App\DTO\TypeData;
use Illuminate\Support\Str;

class CreateTypeAction
{
    public function execute(TypeData $data): Type
    {
        $slug = $data->slug ?? Str::slug($data->name);
        
        // Proteksi duplikasi slug manual di bahasa yang sama
        $count = Type::where('slug', 'like', "{$slug}%")->where('language', $data->language)->count();
        $finalSlug = $count > 0 ? "{$slug}-{$count}" : $slug;

        $attributes = array_filter([
            'name' => $data->name,
            'slug' => $finalSlug,
            'icon' => $data->icon,
            'settings' => $data->settings,
            'promotional_sliders' => $data->promotional_sliders,
            'images' => $data->images,
            'language' => $data->language,
        ], fn($v) => !is_null($v));

        $type = Type::create($attributes);

        // Jika ada input banners, simpan relasinya
        if ($data->banners) {
            $type->banners()->createMany($data->banners);
        }

        return $type;
    }
}