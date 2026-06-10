<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentIntent extends Model
{
    use SoftDeletes;

    protected $table = 'payment_methods';
    protected $guarded = [];

    public function payment_gateways(): BelongsTo
    {
        return $this->BelongsTo(PaymentGateway::class, 'payment_gateway_id');
    }
}