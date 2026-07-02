<?php

declare(strict_types=1);

namespace App\Modules\Author\Actions;

use App\Models\Author;
use Illuminate\Support\Facades\Cache;

final class DeleteAuthorAction
{
    private const CACHE_KEY_PREFIX = 'authors_';

    private const CACHE_KEY_TOP_AUTHORS = 'top_authors_';

    public function execute(Author $author): void
    {
        $language = $author->language;
        $author->delete();

        // Invalidate relevant caches
        Cache::forget(self::CACHE_KEY_PREFIX.$language.'_*');
        Cache::forget(self::CACHE_KEY_TOP_AUTHORS.$language.'_*');
    }
}
