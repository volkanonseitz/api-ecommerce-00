<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'orders';
    protected $guarded = [];
    protected $casts = [
        'shipping_address' => 'json',
        'billing_address' => 'json',
        'payment_intent_info' => 'json',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', fn($q) => $q->orderBy('created_at', 'desc'));
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_product')
            ->withPivot('order_quantity', 'unit_price', 'subtotal', 'variation_option_id')
            ->withTimestamps();
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Order::class, 'parent_id', 'id');
    }

    public function parent_order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_id');
    }

    public function refund(): HasMany
    {
        return $this->hasMany(Refund::class, 'order_id');
    }

    public function wallet_point(): HasMany
    {
        return $this->hasMany(OrderWalletPoint::class, 'order_id');
    }

    public function payment_intent(): HasMany
    {
        return $this->hasMany(PaymentIntent::class, 'order_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'order_id');
    }
}