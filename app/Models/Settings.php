<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $table = 'settings';
    protected $guarded = [];
    protected $casts = [
        'options' => 'json',
    ];

    public static function getData($language = null)
    {
        $lang = $language ?? config('shop.default_language', 'id');
        $data = static::where('language', $lang)->first();
        if (!$data) {
            $data = static::where('language', 'id')->first();
        }
        return $data;
    }
}