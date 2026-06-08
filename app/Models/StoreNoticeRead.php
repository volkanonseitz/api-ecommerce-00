<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class StoreNoticeRead extends Pivot
{
    protected $table = 'store_notice_read';
    protected $guarded = [];
    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function storeNotice(): BelongsTo
    {
        return $this->belongsTo(StoreNotice::class, 'store_notice_id');
    }
}