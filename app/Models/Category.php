<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name', 'slug', 'icon', 'image', 'banner_image',
        'details', 'language', 'parent', 'type_id',
    ];

    protected $casts = [
        'image' => 'json',
        'banner_image' => 'json',
    ];

    protected $appends = ['parent_id'];

    public static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
                $count = static::where('slug', $model->slug)
                    ->where('language', $model->language)
                    ->count();
                if ($count > 0) {
                    $model->slug = $model->slug.'-'.($count + 1);
                }
            }
        });
    }

    public function getParentIdAttribute()
    {
        return $this->parent;
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent', 'id');
    }

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent', 'id');
    }
}
