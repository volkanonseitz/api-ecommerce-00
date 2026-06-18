<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AbusiveReport extends Model
{
    protected $fillable = [
        'user_id',
        'model_id',
        'model_type',
        'message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo(
            __FUNCTION__,
            'model_type',
            'model_id'
        );
    }
}