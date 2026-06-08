<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use SoftDeletes;

    protected $table = 'coupons';
    protected $guarded = [];
    protected $casts = [
        'image' => 'json',
        'active_from' => 'datetime',
        'expire_at' => 'datetime',
        'is_approve' => 'boolean',
    ];
    protected $appends = ['is_valid', 'translated_languages'];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', fn($q) => $q->orderBy('updated_at', 'desc'));
    }

    public function getIsValidAttribute(): bool
    {
        return Carbon::now()->between($this->active_from, $this->expire_at);
    }

    public function getTranslatedLanguagesAttribute(): array
    {
        return static::where('code', $this->code)->pluck('language')->toArray();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}