<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $table = 'refunds';
    protected $guarded = [];
    protected $casts = ['images' => 'json'];

    protected $dispatchesEvents = [
        'created' => \App\Events\RefundRequested::class,
        'updated' => \App\Events\RefundUpdate::class,
    ];

    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function shop(): BelongsTo { return $this->belongsTo(Shop::class); }
    public function refundPolicy(): BelongsTo { return $this->belongsTo(RefundPolicy::class); }
    public function refundReason(): BelongsTo { return $this->belongsTo(RefundReason::class); }
}