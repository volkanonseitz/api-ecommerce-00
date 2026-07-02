<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Actions;

use App\Models\Shipping;
use Illuminate\Support\Facades\Cache;

final class DeleteShippingAction
{
    private const CACHE_KEY_ALL = 'shippings_all';

    public function execute(Shipping $shipping): void
    {
        $shipping->delete();

        Cache::forget(self::CACHE_KEY_ALL); // Invalidate cache
    }
}
