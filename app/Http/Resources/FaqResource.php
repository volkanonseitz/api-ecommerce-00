<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'faq_title' => $this->resource->faq_title,
            'slug' => $this->resource->slug,
            'faq_description' => $this->resource->faq_description,
            'faq_type' => $this->resource->faq_type,
            'issued_by' => $this->resource->issued_by,
            'language' => $this->resource->language,
            'translated_languages' => $this->resource->translated_languages,
            'shop' => $this->resource->whenLoaded(
                'shop',
                fn () => [
                    'id' => $this->resource->shop->id,
                    'name' => $this->resource->shop->name,
                ]
            ),
        ];
    }
}
