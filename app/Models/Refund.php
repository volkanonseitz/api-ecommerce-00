<?php

declare(strict_types=1);

namespace App\Models;

use App\Events\RefundRequested;
use App\Events\RefundUpdate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $table = 'refunds';

    protected $fillable = [
        'order_id',
        'title',
        'description',
        'images',
        'refund_reason_id',
        'customer_id',
        'shop_id',
        'amount',
        'status',
    ];

    protected $casts = ['images' => 'json'];

    protected $dispatchesEvents = [
        'created' => RefundRequested::class,
        'updated' => RefundUpdate::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function refundPolicy(): BelongsTo
    {
        return $this->belongsTo(RefundPolicy::class);
    }

    public function refundReason(): BelongsTo
    {
        return $this->belongsTo(RefundReason::class);
    }
}
