<?php

namespace App\Actions;

use App\Models\Tag;
use App\DTO\TagData;
use Illuminate\Support\Str;

class UpdateTagAction
{
    public function execute(Tag $tag, TagData $data): Tag
    {
        $attributes = array_filter([
            'name' => $data->name,
            'slug' => ($data->slug && $data->slug !== $tag->slug) ? $data->slug : ($data->name ? Str::slug($data->name) : null),
            'type_id' => $data->type_id,
            'icon' => $data->icon,
            'image' => $data->image,
            'details' => $data->details,
            'language' => $data->language,
        ], fn($v) => !is_null($v));

        $tag->update($attributes);
        return $tag->fresh();
    }
}