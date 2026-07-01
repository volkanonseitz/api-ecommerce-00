<?php

declare(strict_types=1);

namespace App\Modules\Type\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TypeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'language' => $this->resource->language,
            'icon' => $this->resource->icon,
            'description' => $this->resource->description,
            'promotional_sliders' => $this->resource->promotional_sliders,
            'images' => $this->resource->images,
            'settings' => $this->resource->settings,
            'translated_languages' => $this->resource->translated_languages,
            'banners' => $this->whenLoaded('banners'),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}