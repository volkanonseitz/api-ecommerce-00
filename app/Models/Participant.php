<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Participant extends Pivot
{
    protected $table = 'participants';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'last_read' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}