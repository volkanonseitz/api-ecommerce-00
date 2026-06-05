<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Author extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'bio',
        'shop_id',
        'image',
        'cover_image',
        'is_approved',
        'language',
    ];

    protected $casts = [
        'image' => 'array',
        'cover_image' => 'array',
        'socials' => 'array',
        'is_approved' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Author $author) {

            if (!$author->slug && $author->name) {
                $author->slug = static::generateUniqueSlug(
                    $author->name,
                    $author->language
                );
            }
        });

        static::updating(function (Author $author) {

            if (
                $author->isDirty('name')
                && empty($author->slug)
            ) {
                $author->slug = static::generateUniqueSlug(
                    $author->name,
                    $author->language
                );
            }
        });
    }

    protected static function generateUniqueSlug(
        string $name,
        ?string $language
    ): string {
        $slug = Str::slug($name);

        $originalSlug = $slug;

        $counter = 1;

        while (
            static::where('slug', $slug)
                ->where('language', $language)
                ->exists()
        ) {
            $slug = $originalSlug.'-'.$counter++;
        }

        return $slug;
    }

    public function products(): HasMany
    {
        return $this->hasMany(
            Product::class,
            'author_id'
        );
    }
}