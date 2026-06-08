<?php

namespace App\Actions;

use App\Models\Tag;
use App\DTO\TagData;
use Illuminate\Support\Str;

class CreateTagAction
{
    public function execute(TagData $data): Tag
    {
        $attributes = array_filter([
            'name' => $data->name,
            'slug' => $data->slug ?? Str::slug($data->name),
            'type_id' => $data->type_id,
            'icon' => $data->icon,
            'image' => $data->image,
            'details' => $data->details,
            'language' => $data->language,
        ], fn($v) => !is_null($v));

        return Tag::create($attributes);
    }
}