<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';
    protected $guarded = [];
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
    protected $appends = [
        'ratings',
        'total_reviews',
        'rating_count',
        'my_review',
        'in_wishlist',
        'blocked_dates',
        'translated_languages',
        'sold'
    ];

    // Helper untuk slug (akan diisi oleh service)
    public function getSlugAttribute($value)
    {
        return $value;
    }

    // Relasi
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

    // Accessors
    public function getRatingsAttribute()
    {
        return round($this->reviews()->avg('rating'), 2);
    }

    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }

    public function getRatingCountAttribute()
    {
        return $this->reviews()->select('rating', DB::raw('count(*) as total'))->groupBy('rating')->get();
    }

    public function getMyReviewAttribute()
    {
        if (auth()->user()) {
            return $this->reviews()->where('user_id', auth()->id())->get();
        }
        return null;
    }

    public function getInWishlistAttribute()
    {
        if (auth()->user()) {
            return $this->wishlists()->where('user_id', auth()->id())->exists();
        }
        return false;
    }

    public function getBlockedDatesAttribute()
    {
        // Implementasi sederhana
        return [];
    }

    public function getTranslatedLanguagesAttribute(): array
    {
        return static::where('slug', $this->slug)->pluck('language')->toArray();
    }

    public function getSoldAttribute()
    {
        return DB::table('order_product')
            ->join('orders', 'orders.id', '=', 'order_product.order_id')
            ->where('order_product.product_id', $this->id)
            ->whereNull('orders.parent_id')
            ->sum('order_quantity');
    }
}