<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    protected $guarded = [];
    protected $casts = [
        'logo' => 'json',
        'cover_image' => 'json',
        'address' => 'json',
        'settings' => 'json',
        'is_active' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function staffs(): HasMany
    {
        return $this->hasMany(User::class, 'shop_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'shop_id');
    }

    public function balance(): HasOne
    {
        return $this->hasOne(Balance::class, 'shop_id');
    }
}