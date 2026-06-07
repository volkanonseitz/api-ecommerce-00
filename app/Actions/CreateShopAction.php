<?php

namespace App\Actions;

use App\Models\Shop;
use App\Models\Balance;
use App\DTO\ShopData;
use Illuminate\Support\Str;

class CreateShopAction
{
    public function execute(ShopData $data): Shop
    {
        $attributes = array_filter([
            'name' => $data->name,
            'slug' => $data->slug ?? Str::slug($data->name),
            'description' => $data->description,
            'cover_image' => $data->cover_image,
            'logo' => $data->logo,
            'is_active' => $data->is_active,
            'address' => $data->address,
            'settings' => $data->settings,
            'notifications' => $data->notifications,
            'owner_id' => $data->owner_id,
        ], fn($v) => !is_null($v));

        $shop = Shop::create($attributes);

        if ($data->categories) {
            $shop->categories()->attach($data->categories);
        }

        if ($data->balance) {
            $balanceData = array_merge($data->balance, ['shop_id' => $shop->id]);
            Balance::create($balanceData);
        }

        return $shop->fresh(['categories', 'balance']);
    }
}