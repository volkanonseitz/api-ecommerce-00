<?php

namespace App\Actions;

use App\Models\Category;
use App\DTO\CategoryData;
use Illuminate\Support\Str;

class CreateCategoryAction
{
    public function execute(CategoryData $data): Category
    {
        $attributes = array_filter([
            'name' => $data->name,
            'slug' => $data->slug ?? Str::slug($data->name),
            'type_id' => $data->type_id,
            'icon' => $data->icon,
            'image' => $data->image,
            'details' => $data->details,
            'banner_image' => $data->banner_image,
            'language' => $data->language,
            'parent' => $data->parent,
        ], fn($v) => !is_null($v));

        return Category::create($attributes);
    }
}