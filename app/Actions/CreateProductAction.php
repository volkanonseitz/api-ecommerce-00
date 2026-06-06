<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\Variation;
use App\DTO\ProductData;
use Illuminate\Support\Facades\DB;

class CreateProductAction
{
    public function execute(ProductData $data, $settings): Product
    {
        return DB::transaction(function () use ($data, $settings) {
            $attributes = $this->prepareAttributes($data);
            $attributes['status'] = $this->determineStatus($data, $settings);
            
            $product = Product::create($attributes);
            
            if ($data->product_type === 'simple') {
                $product->update([
                    'min_price' => $product->price,
                    'max_price' => $product->price,
                ]);
            }
            
            // Amankan dari looping jika metas bukan array
            if (is_array($data->metas)) {
                foreach ($data->metas as $meta) {
                    if (isset($meta['key'])) {
                        $product->setMeta($meta['key'], $meta['value'] ?? null);
                    }
                }
            }
            
            $this->syncRelations($product, $data);
            
            if ($data->variation_options && isset($data->variation_options['upsert'])) {
                $this->handleVariationOptions($product, $data->variation_options['upsert']);
            }
            
            if ($data->is_digital && $data->digital_file) {
                $product->digital_file()->create($data->digital_file);
            }
            
            return $product->fresh();
        });
    }
    
    private function prepareAttributes(ProductData $data): array
    {
        // Untuk CREATE, kita mengambil semua properti dari DTO direct ke array
        $attributes = [
            'name' => $data->name,
            'price' => $data->price,
            'sale_price' => $data->sale_price,
            'max_price' => $data->max_price,
            'min_price' => $data->min_price,
            'type_id' => $data->type_id,
            'shop_id' => $data->shop_id,
            'author_id' => $data->author_id,
            'manufacturer_id' => $data->manufacturer_id,
            'language' => $data->language,
            'product_type' => $data->product_type,
            'quantity' => $data->quantity,
            'unit' => $data->unit,
            'is_digital' => $data->is_digital,
            'is_external' => $data->is_external,
            'external_product_url' => $data->external_product_url,
            'external_product_button_text' => $data->external_product_button_text,
            'description' => $data->description,
            'sku' => $data->sku,
            'image' => $data->image,
            'gallery' => $data->gallery,
            'video' => $data->video,
            'height' => $data->height,
            'length' => $data->length,
            'width' => $data->width,
            'in_stock' => $data->in_stock,
            'is_taxable' => $data->is_taxable,
            'sold_quantity' => $data->sold_quantity,
            'visibility' => $data->visibility,
            'is_rental' => $data->is_rental,
        ];

        // Memanfaatkan helper fungsi generateUniqueSlug yang kamu buat
        $nameForSlug = $data->slug ?: $data->name;
        $attributes['slug'] = generateUniqueSlug(Product::class, $nameForSlug, $data->language);
        
        return $attributes;
    }
    
    private function determineStatus(ProductData $data, $settings): string
    {
        $needsReview = $settings->options['isProductReview'] ?? false;
        if ($needsReview) {
            return $data->status === 'draft' ? 'draft' : 'under_review';
        }
        return $data->status ?? 'publish';
    }
    
    private function syncRelations(Product $product, ProductData $data): void
    {
        if (is_array($data->categories)) $product->categories()->sync($data->categories);
        if (is_array($data->tags)) $product->tags()->sync($data->tags);
        if (is_array($data->dropoff_locations)) $product->dropoff_locations()->sync($data->dropoff_locations);
        if (is_array($data->pickup_locations)) $product->pickup_locations()->sync($data->pickup_locations);
        if (is_array($data->persons)) $product->persons()->sync($data->persons);
        if (is_array($data->features)) $product->features()->sync($data->features);
        if (is_array($data->deposits)) $product->deposits()->sync($data->deposits);
        if (is_array($data->variations)) $product->variations()->sync($data->variations);
    }
    
    private function handleVariationOptions(Product $product, array $variations): void
    {
        foreach ($variations as $variationData) {
            $variation = $product->variation_options()->create($variationData);
            if (($variationData['is_digital'] ?? false) && isset($variationData['digital_file'])) {
                $digitalFile = $variation->digital_file()->create($variationData['digital_file']);
                $variation->update(['digital_file_tracker' => $digitalFile->id]);
            }
        }
    }
}