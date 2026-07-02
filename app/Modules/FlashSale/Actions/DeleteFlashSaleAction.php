<?php

declare(strict_types=1);

namespace App\Modules\FlashSale\Actions;

use App\Models\FlashSale;
use Illuminate\Support\Facades\Cache;

final class DeleteFlashSaleAction
{
    private const CACHE_KEY_PREFIX = 'flash_sales_';

    public function execute(FlashSale $flashSale): void
    {
        $language = $flashSale->language;
        $flashSale->delete();

        // Invalidate relevant caches
        Cache::forget(self::CACHE_KEY_PREFIX.$language.'_*');
    }
}
