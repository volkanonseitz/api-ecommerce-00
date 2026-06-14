<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GetSingleProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type ? ['id' => $this->type->id, 'name' => $this->type->name] : null,
            'language' => $this->language,
            'translated_languages' => $this->translated_languages,
            'product_type' => $this->product_type,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'metas' => $this->metas,
            'digital_file' => $this->digital_file,
            'variations' => $this->whenLoaded('variations', function () {
                return [
                    'id' => $this->variations->id,
                    'slug' => $this->variations->slug,
                    'attribute_id' => $this->variations->attribute_id,
                    'value' => $this->variations->value,
                    'language' => $this->variations->language,
                    'meta' => $this->variations->meta,
                    'translated_languages' => $this->variations->translated_languages,
                    'attribute' => $this->variations->attribute,
                ];
            }),
            'variation_options' => $this->variation_options,
            'shop_id' => $this->shop_id,
            'shop' => $this->shop ? ['id' => $this->shop->id, 'name' => $this->shop->name] : null,
            'author' => $this->author ? ['id' => $this->author->id, 'name' => $this->author->name] : null,
            'manufacturer' => $this->manufacturer ? ['id' => $this->manufacturer->id, 'name' => $this->manufacturer->name] : null,
            'related_products' => RelatedProductResource::collection($this->related_products),
            'description' => $this->description,
            'in_stock' => $this->in_stock,
            'is_taxable' => $this->is_taxable,
            'is_digital' => $this->is_digital,
            'is_external' => $this->is_external,
            'external_product_url' => $this->external_product_url,
            'external_product_button_text' => $this->external_product_button_text,
            'sale_price' => $this->sale_price,
            'max_price' => $this->max_price,
            'min_price' => $this->min_price,
            'ratings' => $this->ratings,
            'total_reviews' => $this->total_reviews,
            'rating_count' => $this->rating_count,
            'my_review' => $this->my_review,
            'in_wishlist' => $this->in_wishlist,
            'sku' => $this->sku,
            'gallery' => $this->gallery,
            'image' => $this->image,
            'video' => $this->video,
            'status' => $this->status,
            'height' => $this->height,
            'length' => $this->length,
            'width' => $this->width,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'in_flash_sale' => $this->in_flash_sale,
        ];
    }
}
