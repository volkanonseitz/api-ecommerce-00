<?php

namespace App\Actions;

use App\DTO\AuthorData;
use App\Models\Author;

class CreateAuthorAction
{
    public function execute(
        AuthorData $data
    ): Author {

        return Author::create([
            'name' => $data->name,
            'slug' => $data->slug,
            'bio' => $data->bio,
            'shop_id' => $data->shop_id,
            'image' => $data->image,
            'cover_image' => $data->cover_image,
            'is_approved' => $data->is_approved,
            'language' => $data->language,
        ]);
    }
}