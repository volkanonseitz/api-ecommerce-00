<?php

declare(strict_types=1);

namespace App\Modules\Faqs\Http\Resources;

use App\Models\Faqs;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Faqs
 */
class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
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
            'shop' => $this->whenLoaded('shop', function () {
                return [
                    'id' => $this->shop->id,
                    'name' => $this->shop->name,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
