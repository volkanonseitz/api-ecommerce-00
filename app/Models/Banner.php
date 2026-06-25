<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banner extends Model
{
    protected $table = 'banners';

    public $guarded = [];

    protected $casts = [
        'image' => 'json',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type_id');
    }
}
