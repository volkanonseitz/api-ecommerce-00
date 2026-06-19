<?php

namespace App\Actions;

use App\DTO\ShippingData;
use App\Models\Shipping;

class UpdateShippingAction
{
    public function execute(Shipping $shipping, ShippingData $data): Shipping
    {
        $shipping->update($data->toArray());

        return $shipping->fresh();
    }
}
