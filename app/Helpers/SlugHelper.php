<?php

if (!function_exists('generateUniqueSlug')) {
    function generateUniqueSlug($model, string $name, string $language, string $field = 'slug', ?int $excludeId = null): string
    {
        $baseSlug = \Illuminate\Support\Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;
        $query = $model::where($field, $slug)->where('language', $language);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $query = $model::where($field, $slug)->where('language', $language);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            $counter++;
        }
        return $slug;
    }
}