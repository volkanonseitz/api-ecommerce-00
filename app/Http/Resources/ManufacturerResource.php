<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ManufacturerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'language' => $this->language,
            'translated_languages' => $this->translated_languages,
            'products_count' => $this->products_count,
            'is_approved' => $this->is_approved,
            'description' => $this->description,
            'website' => $this->website,
            'socials' => $this->socials,
            'image' => $this->image,
            'cover_image' => $this->cover_image,
            'type' => $this->whenLoaded('type', function () {
                return [
                    'id' => $this->type->id,
                    'name' => $this->type->name,
                    'slug' => $this->type->slug,
                ];
            }),
        ];
    }
}
