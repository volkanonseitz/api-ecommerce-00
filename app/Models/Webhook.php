<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

final class Webhook extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'url',
        'secret',
        'events',
        'is_active',
        'last_triggered_at',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        self::creating(function (Webhook $webhook) {
            $webhook->uuid = Str::uuid();
            $webhook->secret = Str::random(40); // Generate a random secret
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
