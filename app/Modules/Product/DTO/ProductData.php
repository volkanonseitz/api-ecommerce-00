<?php

declare(strict_types=1);

namespace App\Modules\Product\DTO;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $slug,
        public readonly ?float $price,
        public readonly ?float $sale_price,
        public readonly ?float $max_price,
        public readonly ?float $min_price,
        public readonly ?int $type_id,
        public readonly ?int $shop_id,
        public readonly ?int $author_id,
        public readonly ?int $manufacturer_id,
        public readonly ?string $language,
        public readonly ?string $product_type,
        public readonly ?int $quantity,
        public readonly ?string $unit,
        public readonly ?bool $is_digital,
        public readonly ?bool $is_external,
        public readonly ?string $external_product_url,
        public readonly ?string $external_product_button_text,
        public readonly ?string $description,
        public readonly ?string $sku,
        public readonly ?array $image,
        public readonly ?array $gallery,
        public readonly ?array $video,
        public readonly ?string $status,
        public readonly ?string $height,
        public readonly ?string $length,
        public readonly ?string $width,
        public readonly ?bool $in_stock,
        public readonly ?bool $is_taxable,
        public readonly ?int $sold_quantity,
        public readonly ?string $visibility,
        public readonly ?array $categories,
        public readonly ?array $tags,
        public readonly ?array $dropoff_locations,
        public readonly ?array $pickup_locations,
        public readonly ?array $persons,
        public readonly ?array $features,
        public readonly ?array $deposits,
        public readonly ?array $metas,
        public readonly ?array $variations,
        public readonly ?array $variation_options,
        public readonly ?array $digital_file,
        public readonly ?bool $inform_purchased_customer,
        public readonly ?string $product_update_message,
        public readonly ?bool $is_rental,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            sale_price: isset($data['sale_price']) ? (float) $data['sale_price'] : null,
            max_price: isset($data['max_price']) ? (float) $data['max_price'] : null,
            min_price: isset($data['min_price']) ? (float) $data['min_price'] : null,
            type_id: $data['type_id'] ?? null,
            shop_id: $data['shop_id'] ?? null,
            author_id: $data['author_id'] ?? null,
            manufacturer_id: $data['manufacturer_id'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'id'),
            product_type: $data['product_type'] ?? null,
            quantity: $data['quantity'] ?? null,
            unit: $data['unit'] ?? null,
            is_digital: isset($data['is_digital']) ? (bool) $data['is_digital'] : null,
            is_external: isset($data['is_external']) ? (bool) $data['is_external'] : null,
            external_product_url: $data['external_product_url'] ?? null,
            external_product_button_text: $data['external_product_button_text'] ?? null,
            description: $data['description'] ?? null,
            sku: $data['sku'] ?? null,
            image: $data['image'] ?? null,
            gallery: $data['gallery'] ?? null,
            video: $data['video'] ?? null,
            status: $data['status'] ?? null,
            height: $data['height'] ?? null,
            length: $data['length'] ?? null,
            width: $data['width'] ?? null,
            in_stock: isset($data['in_stock']) ? (bool) $data['in_stock'] : null,
            is_taxable: isset($data['is_taxable']) ? (bool) $data['is_taxable'] : null,
            sold_quantity: $data['sold_quantity'] ?? 0,
            visibility: $data['visibility'] ?? 'visible',
            categories: $data['categories'] ?? null,
            tags: $data['tags'] ?? null,
            dropoff_locations: $data['dropoff_locations'] ?? null,
            pickup_locations: $data['pickup_locations'] ?? null,
            persons: $data['persons'] ?? null,
            features: $data['features'] ?? null,
            deposits: $data['deposits'] ?? null,
            metas: $data['metas'] ?? null,
            variations: $data['variations'] ?? null,
            variation_options: $data['variation_options'] ?? null,
            digital_file: $data['digital_file'] ?? null,
            inform_purchased_customer: (bool) ($data['inform_purchased_customer'] ?? false),
            product_update_message: $data['product_update_message'] ?? null,
            is_rental: isset($data['is_rental']) ? (bool) $data['is_rental'] : null,
        );
    }



    public static function fromArray(array $data): self
    {
        // Validation rules for DTO creation
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug',
            'price' => 'required|numeric|min:0',
            'shop_id' => 'required|integer|exists:shops,id',
            'status' => 'required|in:publish,draft,private',
            'quantity' => 'required|integer|min:0',
            'language' => 'required|string|size:2',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            sale_price: isset($data['sale_price']) ? (float) $data['sale_price'] : null,
            max_price: isset($data['max_price']) ? (float) $data['max_price'] : null,
            min_price: isset($data['min_price']) ? (float) $data['min_price'] : null,
            type_id: $data['type_id'] ?? null,
            shop_id: $data['shop_id'] ?? null,
            author_id: $data['author_id'] ?? null,
            manufacturer_id: $data['manufacturer_id'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'id'),
            product_type: $data['product_type'] ?? null,
            quantity: $data['quantity'] ?? null,
            unit: $data['unit'] ?? null,
            is_digital: isset($data['is_digital']) ? (bool) $data['is_digital'] : null,
            is_external: isset($data['is_external']) ? (bool) $data['is_external'] : null,
            external_product_url: $data['external_product_url'] ?? null,
            external_product_button_text: $data['external_product_button_text'] ?? null,
            description: $data['description'] ?? null,
            sku: $data['sku'] ?? null,
            image: $data['image'] ?? null,
            gallery: $data['gallery'] ?? null,
            video: $data['video'] ?? null,
            status: $data['status'] ?? null,
            height: $data['height'] ?? null,
            length: $data['length'] ?? null,
            width: $data['width'] ?? null,
            in_stock: isset($data['in_stock']) ? (bool) $data['in_stock'] : null,
            is_taxable: isset($data['is_taxable']) ? (bool) $data['is_taxable'] : null,
            sold_quantity: $data['sold_quantity'] ?? 0,
            visibility: $data['visibility'] ?? 'visible',
            categories: $data['categories'] ?? null,
            tags: $data['tags'] ?? null,
            dropoff_locations: $data['dropoff_locations'] ?? null,
            pickup_locations: $data['pickup_locations'] ?? null,
            persons: $data['persons'] ?? null,
            features: $data['features'] ?? null,
            deposits: $data['deposits'] ?? null,
            metas: $data['metas'] ?? null,
            variations: $data['variations'] ?? null,
            variation_options: $data['variation_options'] ?? null,
            digital_file: $data['digital_file'] ?? null,
            inform_purchased_customer: (bool) ($data['inform_purchased_customer'] ?? false),
            product_update_message: $data['product_update_message'] ?? null,
            is_rental: isset($data['is_rental']) ? (bool) $data['is_rental'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'max_price' => $this->max_price,
            'min_price' => $this->min_price,
            'type_id' => $this->type_id,
            'shop_id' => $this->shop_id,
            'author_id' => $this->author_id,
            'manufacturer_id' => $this->manufacturer_id,
            'language' => $this->language,
            'product_type' => $this->product_type,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'is_digital' => $this->is_digital,
            'is_external' => $this->is_external,
            'external_product_url' => $this->external_product_url,
            'external_product_button_text' => $this->external_product_button_text,
            'description' => $this->description,
            'sku' => $this->sku,
            'image' => $this->image,
            'gallery' => $this->gallery,
            'video' => $this->video,
            'status' => $this->status,
            'height' => $this->height,
            'length' => $this->length,
            'width' => $this->width,
            'in_stock' => $this->in_stock,
            'is_taxable' => $this->is_taxable,
            'sold_quantity' => $this->sold_quantity,
            'visibility' => $this->visibility,
            'categories' => $this->categories,
            'tags' => $this->tags,
            'dropoff_locations' => $this->dropoff_locations,
            'pickup_locations' => $this->pickup_locations,
            'persons' => $this->persons,
            'features' => $this->features,
            'deposits' => $this->deposits,
            'metas' => $this->metas,
            'variations' => $this->variations,
            'variation_options' => $this->variation_options,
            'digital_file' => $this->digital_file,
            'inform_purchased_customer' => $this->inform_purchased_customer,
            'product_update_message' => $this->product_update_message,
            'is_rental' => $this->is_rental,
        ];
    }

    public function getShopId(): ?int
    {
        return $this->shop_id;
    }

    public function isDigital(): bool
    {
        return $this->is_digital === true;
    }

    public function isRental(): bool
    {
        return $this->is_rental === true;
    }
}
