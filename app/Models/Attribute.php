<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Attribute extends Model
{
    protected $table = 'attributes';

    protected $fillable = [
        'name',
        'slug',
        'language',
        'shop_id',
        'translation_group',
    ];

    protected $appends = [
        'translated_languages',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $attribute) {

            if (empty($attribute->translation_group)) {
                $attribute->translation_group = (string) Str::uuid();
            }

            if (empty($attribute->slug)) {
                $attribute->slug = static::generateUniqueSlug(
                    $attribute->name,
                    $attribute->language
                );
            }
        });
    }

    protected static function generateUniqueSlug(
        string $name,
        string $language
    ): string {
        $baseSlug = Str::slug($name);

        $slug = $baseSlug;
        $counter = 1;

        while (
            static::where('slug', $slug)
                ->where('language', $language)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public function values(): HasMany
    {
        return $this->hasMany(
            AttributeValue::class,
            'attribute_id'
        );
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(
            Shop::class,
            'shop_id'
        );
    }

    public function translations()
    {
        return static::query()
            ->where(
                'translation_group',
                $this->translation_group
            );
    }

    public function getTranslatedLanguagesAttribute(): array
    {
        if (! $this->translation_group) {
            return [];
        }

        return static::query()
            ->where(
                'translation_group',
                $this->translation_group
            )
            ->pluck('language')
            ->toArray();
    }
}
