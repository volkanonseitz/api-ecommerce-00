<?php

declare(strict_types=1);

namespace App\Modules\Shop\Actions;

use App\Models\Balance;
use App\Models\Product;
use App\Models\Shop;
use App\Modules\Shop\DTO\ApproveShopData;
use Illuminate\Support\Facades\DB;

final class ApproveShopAction
{
    public function execute(Shop $shop, ApproveShopData $data): Shop
    {
        DB::transaction(function () use ($shop, $data): void {
            $shop->forceFill(['is_active' => true])->save();

            Product::where('shop_id', $shop->id)->update(['status' => 'publish']);

            $balance = Balance::firstOrNew(['shop_id' => $shop->id]);
            $balance->admin_commission_rate = $data->is_custom_commission
                ? $data->admin_commission_rate
                : $shop->getDefaultCommissionRate($balance->total_earnings ?? 0.0);
            $balance->is_custom_commission = $data->is_custom_commission;
            $balance->save();
        });

        return $shop->fresh(['balance']);
    }
}
