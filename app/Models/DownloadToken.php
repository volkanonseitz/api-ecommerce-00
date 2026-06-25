<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadToken extends Model
{
    protected $table = 'download_tokens';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(DigitalFile::class, 'digital_file_id');
    }
}
