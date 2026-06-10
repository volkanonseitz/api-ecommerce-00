<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentIntent extends Model
{
    use SoftDeletes;

    protected $table = 'payment_gateways';
    protected $guarded = [];

    public function payment_methods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function users(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'user_id');
    }
}