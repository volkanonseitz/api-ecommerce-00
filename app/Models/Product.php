<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'name', 'slug', 'price', 'sale_price', 'max_price', 'min_price',
        'description', 'sku', 'quantity', 'unit', 'language',
        'image', 'gallery', 'video', 'status', 'product_type',
        'is_rental', 'is_digital', 'is_external', 'in_stock', 'is_taxable',
        'shop_id', 'type_id', 'author_id', 'manufacturer_id', 'shipping_id',
        'sold_quantity', 'visibility',
    ];

    protected $casts = [
        'image' => 'json',
        'gallery' => 'json',
        'video' => 'json',
        'is_rental' => 'boolean',
        'is_digital' => 'boolean',
        'is_external' => 'boolean',
        'in_stock' => 'boolean',
        'is_taxable' => 'boolean',
        'in_flash_sale' => 'boolean',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function variation_options(): HasMany
    {
        return $this->hasMany(Variation::class, 'product_id');
    }

    public function variations(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'attribute_product');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function digital_file(): MorphOne
    {
        return $this->morphOne(DigitalFile::class, 'fileable');
    }

    public function availabilities(): MorphMany
    {
        return $this->morphMany(Availability::class, 'bookable');
    }

    public function dropoff_locations(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'dropoff_location_product', 'product_id', 'resource_id');
    }

    public function pickup_locations(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'pickup_location_product', 'product_id', 'resource_id');
    }

    public function deposits(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'deposit_product', 'product_id', 'resource_id');
    }

    public function persons(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'person_product', 'product_id', 'resource_id');
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'feature_product', 'product_id', 'resource_id');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_product')
            ->withPivot('order_quantity', 'unit_price', 'subtotal', 'variation_option_id')
            ->withTimestamps();
    }

    public function shipping(): BelongsTo
    {
        return $this->belongsTo(Shipping::class);
    }

    public function flashSales(): BelongsToMany
    {
        return $this->belongsToMany(FlashSale::class, 'flash_sale_products')->withPivot('flash_sale_id', 'product_id');
    }
}
