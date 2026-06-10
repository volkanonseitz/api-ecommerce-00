<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    protected $table = 'commissions';
    protected $guarded = [];
    protected $casts = ['image' => 'json',];
}