<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    protected $table = 'balances';
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}