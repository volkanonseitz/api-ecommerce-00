<?php

namespace App\Actions;

use App\Models\Category;
use App\DTO\CategoryData;
use Illuminate\Support\Str;

class UpdateCategoryAction
{
    public function execute(Category $category, CategoryData $data): Category
    {
        $attributes = array_filter([
            'name' => $data->name,
            'slug' => ($data->slug && $data->slug !== $category->slug) ? $data->slug : ($data->name ? Str::slug($data->name) : null),
            'type_id' => $data->type_id,
            'icon' => $data->icon,
            'image' => $data->image,
            'details' => $data->details,
            'banner_image' => $data->banner_image,
            'language' => $data->language,
            'parent' => $data->parent,
        ], fn($v) => !is_null($v));

        $category->update($attributes);
        return $category->fresh();
    }
}