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
            'id' => $this->id,
            'name' => $this->name,
            'language' => $this->language,
            'is_approved' => $this->is_approved,
            'slug' => $this->slug,
            'bio' => $this->bio,
            'quote' => $this->quote,
            'born' => $this->born,
            'death' => $this->death,
            'languages' => $this->languages,
            'socials' => $this->socials,
            'image' => $this->image,
            'cover_image' => $this->cover_image,
            'products_count' => $this->products_count ?? 0,
        ];
    }
}