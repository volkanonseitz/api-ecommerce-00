<?php

namespace App\Actions;

use App\Models\Shop;
use App\Models\Balance;
use App\DTO\ShopData;
use Illuminate\Support\Str;

class UpdateShopAction
{
    public function execute(Shop $shop, ShopData $data): Shop
    {
        $attributes = array_filter([
            'name' => $data->name,
            'slug' => ($data->slug && $data->slug !== $shop->slug) ? $data->slug : ($data->name ? Str::slug($data->name) : null),
            'description' => $data->description,
            'cover_image' => $data->cover_image,
            'logo' => $data->logo,
            'is_active' => $data->is_active,
            'address' => $data->address,
            'settings' => $data->settings,
            'notifications' => $data->notifications,
        ], fn($v) => !is_null($v));

        $shop->update($attributes);

        if ($data->categories !== null) {
            $shop->categories()->sync($data->categories);
        }

        if ($data->balance !== null) {
            $balance = $shop->balance ?? new Balance(['shop_id' => $shop->id]);
            $balance->fill($data->balance);
            $balance->save();
        }

        return $shop->fresh(['categories', 'balance']);
    }
}