<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BecameSeller extends Model
{
    use HasFactory;

    protected $table = 'became_sellers';

    protected $guarded = [];

    protected $casts = [
        'page_options' => 'json',
    ];

    public static function getData($language = null)
    {
        $lang = $language ?? config('shop.default_language', 'id');
        $data = static::where('language', $lang)->first();
        if (! $data) {
            $data = static::where('language', config('shop.default_language', 'id'))->first();
        }

        return $data;
    }
}
