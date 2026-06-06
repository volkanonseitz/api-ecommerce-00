<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\Variation;
use App\Models\DigitalFile;
use App\DTO\ProductData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Events\DigitalProductUpdateEvent;

class UpdateProductAction
{
    public function execute(Product $product, ProductData $data, $settings): Product
    {
        return DB::transaction(function () use ($product, $data, $settings) {
            // Prepare attributes
            $attributes = $this->prepareAttributes($product, $data);
            
            // Update status based on review setting
            if (isset($settings->options['isProductReview']) && $settings->options['isProductReview']) {
                $attributes['status'] = $this->checkProductForPublish($data, $product);
            }
            
            $product->update($attributes);
            
            // Handle metas
            if ($data->metas) {
                foreach ($data->metas as $meta) {
                    $product->setMeta($meta['key'], $meta['value']);
                }
            }
            
            // Sync relations
            $this->syncRelations($product, $data);
            
            // Handle variation options (upsert + delete)
            if ($data->variation_options) {
                if (isset($data->variation_options['upsert'])) {
                    $this->upsertVariationOptions($product, $data->variation_options['upsert'], $settings);
                }
                if (isset($data->variation_options['delete'])) {
                    $product->variation_options()->whereIn('id', $data->variation_options['delete'])->delete();
                }
            }
            
            // Handle digital file for product
            if ($data->digital_file) {
                if ($product->digital_file) {
                    $product->digital_file()->update($data->digital_file);
                } else {
                    $product->digital_file()->create($data->digital_file);
                }
            }
            
            // If simple product, delete variations
            if ($data->product_type === 'simple') {
                $product->variations()->detach();
                $product->variation_options()->delete();
                $product->update([
                    'min_price' => $product->price,
                    'max_price' => $product->price,
                ]);
            }
            
            // Fire digital product update event if needed
            if (($settings->options['enableEmailForDigitalProduct'] ?? false) && $data->inform_purchased_customer) {
                event(new DigitalProductUpdateEvent($product, auth()->user(), [
                    'inform_customer' => $data->inform_purchased_customer,
                    'update_message' => $data->product_update_message,
                ]));
            }
            
            return $product->fresh();
        });
    }
    
    private function prepareAttributes(Product $product, ProductData $data): array
    {
        // Jangan gunakan array_filter jika ingin mendukung pengosongan data (nullify)
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
            'language' => $data->language ?? $product->language,
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

        // Gunakan helper generateUniqueSlug jika slug berubah
        if (!empty($data->slug) && $data->slug !== $product->slug) {
            $language = $data->language ?? $product->language;
            $attributes['slug'] = generateUniqueSlug(Product::class, $data->slug, $language, 'slug', $product->id);
        }

        return $attributes;
    }
    
    private function checkProductForPublish(ProductData $data, Product $product): string
    {
        $user = auth()->user();
        $status = $product->status;
        
        if ($user->hasPermissionTo('store_owner') && $product->shop->owner_id === $user->id) {
            if (in_array($product->status, ['draft', 'under_review', 'rejected'])) {
                $status = $data->status === 'draft' ? 'draft' : 'under_review';
            } else {
                $status = $data->status === 'publish' ? 'publish' : 'unpublish';
            }
        } elseif ($user->hasPermissionTo('super_admin')) {
            if ($data->status === 'approved') {
                $status = 'publish';
                event(new \App\Events\ProductReviewApproved($product));
            } elseif ($data->status === 'rejected') {
                $status = 'rejected';
                event(new \App\Events\ProductReviewRejected($product));
            } elseif ($data->status === 'publish') {
                $status = 'publish';
            } elseif ($data->status === 'unpublish') {
                $status = 'unpublish';
            } else {
                $status = 'rejected';
            }
        }
        return $status;
    }
    
    private function syncRelations(Product $product, ProductData $data): void
    {
        if ($data->categories !== null) $product->categories()->sync($data->categories);
        if ($data->tags !== null) $product->tags()->sync($data->tags);
        if ($data->dropoff_locations !== null) $product->dropoff_locations()->sync($data->dropoff_locations);
        if ($data->pickup_locations !== null) $product->pickup_locations()->sync($data->pickup_locations);
        if ($data->persons !== null) $product->persons()->sync($data->persons);
        if ($data->features !== null) $product->features()->sync($data->features);
        if ($data->deposits !== null) $product->deposits()->sync($data->deposits);
        if ($data->variations !== null) $product->variations()->sync($data->variations);
    }
    
    private function upsertVariationOptions(Product $product, array $variations, $settings): void
{
    foreach ($variations as $rawVariationData) {
        // 1. Transformasikan array mentah ke DTO untuk type-safety
        $variationDto = VariationOptionData::fromArray($rawVariationData);
        
        // 2. Petakan data yang akan disimpan ke database
        $variationData = [
            'sku'        => $variationDto->sku,
            'price'      => $variationDto->price,
            'sale_price' => $variationDto->sale_price,
            'quantity'   => $variationDto->quantity,
            'options'    => $variationDto->options,
            'is_digital' => $variationDto->is_digital,
        ];
        
        // 3. Filter data agar field yang bernilai null (tidak dikirim di request PATCH) 
        //    tidak menimpa data yang sudah ada di database.
        $variationData = array_filter($variationData, fn($value) => !is_null($value));

        // 4. Proses Update jika ID variasi dikirim dan cocok dengan produk
        if ($variationDto->id) {
            $variation = Variation::find($variationDto->id);
            
            if ($variation && $variation->product_id === $product->id) {
                $variation->update($variationData);
                
                // Tangani file digital untuk variasi yang di-update
                if ($variationDto->is_digital) {
                    if ($variation->digital_file && $variationDto->digital_file) {
                        // Jika file lama ada, lakukan update
                        $variation->digital_file()->update($variationDto->digital_file);
                    } elseif ($variationDto->digital_file) {
                        // Jika file lama belum ada, buat baru dan track ID-nya
                        $digital = $variation->digital_file()->create($variationDto->digital_file);
                        $variation->update(['digital_file_tracker' => $digital->id]);
                    }
                }
            }
        } 
        // 5. Proses Create jika tidak ada ID variasi (Variasi Baru)
        else {
            $variation = $product->variation_options()->create($variationData);
            
            // Tangani file digital untuk variasi baru
            if ($variationDto->is_digital && $variationDto->digital_file) {
                $digital = $variation->digital_file()->create($variationDto->digital_file);
                $variation->update(['digital_file_tracker' => $digital->id]);
            }
        }
        
        // 6. Sinkronisasi data ke variasi bahasa lain jika fitur translasi aktif
        if ($variation && config('shop.translation_enabled')) {
            Variation::where('sku', $variation->sku)
                ->where('id', '!=', $variation->id)
                ->update(array_filter([
                    'price'      => $variation->price,
                    'sale_price' => $variation->sale_price,
                    'quantity'   => $variation->quantity,
                ], fn($value) => !is_null($value)));
        }
    }
}
}