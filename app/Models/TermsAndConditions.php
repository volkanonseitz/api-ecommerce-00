<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TermsAndConditions extends Model
{
    protected $table = 'terms_and_conditions';
    protected $guarded = [];
    protected $appends = ['translated_languages'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
                $count = static::where('slug', $model->slug)->where('language', $model->language)->count();
                if ($count) {
                    $model->slug = $model->slug . '-' . ($count + 1);
                }
            }
        });
    }

    public function getTranslatedLanguagesAttribute()
    {
        return static::where('slug', $this->slug)->pluck('language')->toArray();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}