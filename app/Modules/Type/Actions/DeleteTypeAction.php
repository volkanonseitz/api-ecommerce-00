<?php

declare(strict_types=1);

namespace App\Modules\Type\Actions;

use App\Models\Type;
use Illuminate\Support\Facades\Cache;

final class DeleteTypeAction
{
    private const CACHE_KEY_PREFIX = 'types_';

    public function execute(Type $type): void
    {
        $language = $type->language;
        $type->banners()->delete(); // Delete related banners
        $type->delete();

        Cache::forget(self::CACHE_KEY_PREFIX.$language.'_*'); // Invalidate cache
    }
}
