<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $table = 'address';

    protected $fillable = [
        'title',
        'type',
        'default',
        'address',
        'location',
        'customer_id',
    ];

    protected $casts = [
        'default' => 'boolean',
        'address' => 'array',
        'location' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
