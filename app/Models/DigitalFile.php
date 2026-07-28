<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DigitalFile extends Model
{
    use HasFactory;

    protected $table = 'digital_files';

    protected $guarded = [];

    protected $hidden = ['url']; // sembunyikan url saat serialisasi

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    public function orderedFile(): HasOne
    {
        return $this->hasOne(OrderedFile::class, 'digital_file_id');
    }
}
