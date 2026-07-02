<?php

declare(strict_types=1);

namespace App\Modules\DeliveryTime\Actions;

use App\Models\DeliveryTime;
use Illuminate\Support\Facades\Cache;

final class DeleteDeliveryTimeAction
{
    public function execute(DeliveryTime $deliveryTime): void
    {
        $language = $deliveryTime->language;
        $deliveryTime->delete();

        Cache::forget("delivery_times_{$language}"); // Invalidate cache
    }
}
