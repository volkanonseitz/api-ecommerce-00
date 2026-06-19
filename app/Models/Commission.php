<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $table = 'commissions';

    protected $guarded = [];

    protected $casts = ['image' => 'json'];
}
