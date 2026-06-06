<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manufacturer extends Model
{
    protected $table = 'manufacturers';
    protected $guarded = [];
    
    protected $casts = [
        'image' => 'json',
        'cover_image' => 'json',
        'socials' => 'json',
        'is_approved' => 'boolean',
    ];
    
    protected $appends = ['products_count', 'translated_languages'];

    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }

    public function getTranslatedLanguagesAttribute(): array
    {
        return static::where('slug', $this->slug)->pluck('language')->toArray();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'manufacturer_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type_id');
    }
}