<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Variation extends Model
{
    protected $table = 'variation_options';
    protected $guarded = [];
    protected $casts = [
        'options' => 'json',
        'blocked_dates' => 'json',
        'is_digital' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function digital_file(): MorphOne
    {
        return $this->morphOne(DigitalFile::class, 'fileable');
    }

    public function availabilities(): MorphMany
    {
        return $this->morphMany(Availability::class, 'bookable');
    }
}