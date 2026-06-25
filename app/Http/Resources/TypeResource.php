<?php

namespace App\Http\Resources;

use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Type
 */
class TypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'icon' => $this->resource->icon,
            'language' => $this->resource->language,
            'translated_languages' => $this->resource->translated_languages,
            'settings' => $this->resource->settings,
            'promotional_sliders' => $this->resource->promotional_sliders,
            'images' => $this->resource->images,
            'banners' => $this->resource->whenLoaded('banners'),
        ];
    }
}
