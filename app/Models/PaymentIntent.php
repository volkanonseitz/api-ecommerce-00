<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentIntent extends Model
{
    use SoftDeletes;

    protected $table = 'payment_intents';
    protected $guarded = [];
    protected $casts = [
        'payment_intent_info' => 'json',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}