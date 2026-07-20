<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'payment_status',
        'order_status',
        'delivery_time',
        'shipping_address',
        'billing_address',
        'coupon_id',
        'shop_id',
        'parent_id',
        'note',
        'language',
        'cancelled_amount',
        'cancelled_tax',
        'cancelled_delivery_fee',
        'discount',
        'payment_gateway',
        'altered_payment_gateway',
        'logistics_provider_id', // Matched to migration, assuming logistics table
        'delivery_fee',
        // Removed: customer_type, payment_intent, payment_method, payment_provider, payment_note, shipping_address_id, shipping_method_id, currency, conversion_rate, tax, subtotal, admin_revenue, shop_revenue, commission_rate, payment_intent_info, wallet_point, wallet_point_total, is_guest, cancelled_at, shipping_cost, customer_email, customer_phone, customer_country, customer_city, customer_state, customer_zip, product_id


    ];

    protected $casts = [
        'shipping_address' => 'json',
        'billing_address' => 'json',
        'cancelled_amount' => 'decimal:2',
        'cancelled_tax' => 'decimal:2',
        'cancelled_delivery_fee' => 'decimal:2',
        'amount' => 'decimal:2',
        'sales_tax' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'total' => 'decimal:2',
        'discount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
    ];

    protected $hidden = [
        'customer_phone', // Keep if this is intended to be hidden
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', fn ($q) => $q->orderBy('created_at', 'desc'));
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_product')
            ->withPivot('order_quantity', 'unit_price', 'subtotal', 'variation_option_id')
            ->withTimestamps();
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Order::class, 'parent_id', 'id');
    }

    public function parent_order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_id'); // Still needs 'parent_id' for self-referencing
    }

    public function refund(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function wallet_point(): HasMany
    {
        return $this->hasMany(OrderWalletPoint::class);
    }

    public function payment_intent(): HasMany
    {
        return $this->hasMany(PaymentIntent::class);
    }
}
