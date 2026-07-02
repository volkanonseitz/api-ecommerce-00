<?php

declare(strict_types=1);

namespace App\Modules\Category\Actions;

use Illuminate\Support\Facades\Cache;

final class ClearCategoryCacheAction
{
    public function execute(?string $language = null): void
    {
        if ($language) {
            Cache::forget("categories_{$language}_*");
            Cache::forget("category_*_{$language}");
        }
        Cache::forget('featured_categories_*');
    }
}
