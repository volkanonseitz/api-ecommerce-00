<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotifyLogs extends Model
{
    use SoftDeletes;

    protected $table = 'notify_logs';
    protected $guarded = [];
    protected $hidden = ['updated_at', 'deleted_at'];

    public function receiverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver')->with('profile');
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender')->with('profile');
    }
}