<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Faqs extends Model
{
    protected $table = 'faqs';
    protected $guarded = [];
    protected $appends = ['translated_languages'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->faq_title);
                $count = static::where('slug', $model->slug)->where('language', $model->language)->count();
                if ($count > 0) {
                    $model->slug = $model->slug . '-' . ($count + 1);
                }
            }
        });
        static::updating(function ($model) {
            if ($model->isDirty('faq_title') && empty($model->slug)) {
                $model->slug = Str::slug($model->faq_title);
                $count = static::where('slug', $model->slug)->where('language', $model->language)->where('id', '!=', $model->id)->count();
                if ($count > 0) {
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