<?php

declare(strict_types=1);

namespace App\Modules\Category\Actions;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

final class DeleteCategoryAction
{
    public function execute(Category $category): void
    {
        $language = $category->language;
        $category->delete();

        // Invalidate relevant caches
        Cache::forget("categories_{$language}_*");
        Cache::forget("category_*_{$language}");
        Cache::forget('featured_categories_*');
    }
}
