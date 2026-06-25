<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'language' => $this->resource->language,
            'is_approved' => $this->resource->is_approved,
            'slug' => $this->resource->slug,
            'bio' => $this->resource->bio,
            'quote' => $this->resource->quote,
            'born' => $this->resource->born,
            'death' => $this->resource->death,
            'languages' => $this->resource->languages,
            'socials' => $this->resource->socials,
            'image' => $this->resource->image,
            'cover_image' => $this->resource->cover_image,
            'products_count' => $this->resource->products_count ?? 0,
        ];
    }
}
