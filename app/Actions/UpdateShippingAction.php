<?php

namespace App\Actions;

use App\Models\Shipping;
use App\DTO\ShippingData;

class UpdateShippingAction
{
    public function execute(Shipping $shipping, ShippingData $data): Shipping
    {
        $shipping->update($data->toArray());
        return $shipping->fresh();
    }
}