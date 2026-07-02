<?php

declare(strict_types=1);

namespace App\Modules\Tax\Actions;

use App\Models\Tax;
use Illuminate\Support\Facades\Cache;

final class DeleteTaxAction
{
    private const CACHE_KEY_ALL = 'taxes_all';

    public function execute(Tax $tax): void
    {
        $tax->delete();

        Cache::forget(self::CACHE_KEY_ALL); // Invalidate cache
    }
}
