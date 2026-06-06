<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'language' => $this->language,
            'translated_languages' => $this->translated_languages,
            'settings' => $this->settings,
            'promotional_sliders' => $this->promotional_sliders,
            'images' => $this->images,
            'banners' => $this->whenLoaded('banners'),
        ];
    }
}