<?php

namespace App\Actions;

use App\DTO\AuthorData;
use App\Models\Author;

class UpdateAuthorAction
{
    public function execute(
        Author $author,
        AuthorData $data
    ): Author {

        $attributes = array_filter([
            'name' => $data->name,
            'slug' => $data->slug,
            'bio' => $data->bio,
            'shop_id' => $data->shop_id,
            'image' => $data->image,
            'cover_image' => $data->cover_image,
            'is_approved' => $data->is_approved,
            'language' => $data->language,
        ], fn($value) => $value !== null);

        $author->update($attributes);

        return $author->fresh();
    }
}