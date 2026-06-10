<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipping extends Model
{
    protected $table = 'shipping_classes';
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', fn($q) => $q->orderBy('updated_at', 'desc'));
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'shipping_class_id');
    }
}