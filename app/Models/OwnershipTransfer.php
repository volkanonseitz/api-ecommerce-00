<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class OwnershipTransfer extends Model
{
    use SoftDeletes;

    protected $table = 'ownership_transfers';
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->transaction_identifier = static::generateTransactionId();
            $model->created_by = Auth::id();
        });
    }

    public static function generateTransactionId(): string
    {
        $date = now()->format('Y-m-d');
        $count = static::whereDate('created_at', now()->toDateString())->count() + 1;
        return $date . '-' . sprintf('%04d', $count);
    }

    public function previousOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from')->with('profile');
    }

    public function currentOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to')->with('profile');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id')->with(['balance', 'refunds', 'withdraws']);
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}