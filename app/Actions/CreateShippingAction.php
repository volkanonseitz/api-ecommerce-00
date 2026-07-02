<?php

namespace App\Actions;

use App\Models\Shipping;
use App\Modules\Shipping\DTO\ShippingData;

class CreateShippingAction
{
    public function execute(ShippingData $data): Shipping
    {
        return Shipping::create($data->toArray());
    }
}
