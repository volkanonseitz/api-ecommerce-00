<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'language' => $this->resource->language,
            'translated_languages' => $this->resource->translated_languages,
            'slug' => $this->resource->slug,
            'details' => $this->resource->details,
            'image' => $this->resource->image,
            'icon' => $this->resource->icon,
            'type' => $this->resource->whenLoaded('type', fn () => ['id' => $this->resource->type->id, 'name' => $this->resource->type->name]),
        ];
    }
}
