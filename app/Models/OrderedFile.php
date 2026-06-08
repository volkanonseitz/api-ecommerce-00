<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderedFile extends Model
{
    protected $table = 'ordered_files';
    protected $guarded = [];

    public function file(): BelongsTo
    {
        return $this->belongsTo(DigitalFile::class, 'digital_file_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'tracking_number', 'tracking_number');
    }
}