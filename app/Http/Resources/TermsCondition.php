<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TermsConditionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => $this->type,
            'issued_by' => $this->issued_by,
            'is_approved' => $this->is_approved,
            'language' => $this->language,
            'translated_languages' => $this->translated_languages,
        ];
    }
}