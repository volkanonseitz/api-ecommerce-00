<?php

declare(strict_types=1);

namespace App\Modules\Attribute\Actions;

use App\Models\Attribute;
use Illuminate\Support\Facades\Cache;

final class DeleteAttributeAction
{
    private const CACHE_KEY_PREFIX = 'attributes_';

    public function execute(Attribute $attribute): void
    {
        $language = $attribute->language;
        $attribute->delete();

        Cache::forget(self::CACHE_KEY_PREFIX.$language); // Invalidate cache
    }
}
