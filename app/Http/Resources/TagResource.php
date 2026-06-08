<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'language' => $this->language,
            'translated_languages' => $this->translated_languages,
            'slug' => $this->slug,
            'details' => $this->details,
            'image' => $this->image,
            'icon' => $this->icon,
            'type' => $this->whenLoaded('type', fn() => ['id' => $this->type->id, 'name' => $this->type->name]),
        ];
    }
}