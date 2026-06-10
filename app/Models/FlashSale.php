<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FlashSale extends Model
{
    use SoftDeletes;

    protected $table = 'flash_sales';
    protected $guarded = [];
    protected $casts = [
        'cover_image' => 'json',
        'sale_builder' => 'json',
        'image' => 'json',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
    protected $appends = ['translated_languages'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
                $count = static::where('slug', $model->slug)->where('language', $model->language)->count();
                if ($count > 0) {
                    $model->slug = $model->slug . '-' . ($count + 1);
                }
            }
        });
        static::updating(function ($model) {
            if ($model->isDirty('title') && empty($model->slug)) {
                $model->slug = Str::slug($model->title);
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

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'flash_sale_products')->withPivot('flash_sale_id', 'product_id');
    }
}