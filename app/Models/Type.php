<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Type extends Model
{
    use HasFactory;

    protected $table = 'types';

    protected $fillable = [
        'name',
        'slug',
        'language',
        'promotional_sliders',
        'images',
        'settings',
        'icon',
        'description',
    ];

    protected $casts = [
        'promotional_sliders' => 'json',
        'images' => 'json',
        'settings' => 'json',
    ];

    protected $appends = ['translated_languages'];

    // Pengganti TranslationTrait untuk mengambil bahasa yang tersedia
    public function getTranslatedLanguagesAttribute(): array
    {
        return static::where('slug', $this->slug)->pluck('language')->toArray();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'type_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'type_id');
    }

    public function banners(): HasMany
    {
        return $this->hasMany(Banner::class, 'type_id'); // Hubungan hasMany sesuai struktur lama
    }
}
