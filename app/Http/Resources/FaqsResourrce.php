<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'faq_title' => $this->faq_title,
            'slug' => $this->slug,
            'faq_description' => $this->faq_description,
            'faq_type' => $this->faq_type,
            'issued_by' => $this->issued_by,
            'language' => $this->language,
            'translated_languages' => $this->translated_languages,
            'shop' => $this->whenLoaded('shop', fn() => ['id' => $this->shop->id, 'name' => $this->shop->name]),
        ];
    }
}