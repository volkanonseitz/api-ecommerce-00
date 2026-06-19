<?php

namespace App\Actions;

use App\DTO\ShippingData;
use App\Models\Shipping;

class CreateShippingAction
{
    public function execute(ShippingData $data): Shipping
    {
        return Shipping::create($data->toArray());
    }
}
