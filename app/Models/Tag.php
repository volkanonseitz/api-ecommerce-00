<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $table = 'tags';
    protected $guarded = [];
    protected $casts = ['image' => 'json'];
    protected $appends = ['translated_languages'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
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

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tag');
    }
}