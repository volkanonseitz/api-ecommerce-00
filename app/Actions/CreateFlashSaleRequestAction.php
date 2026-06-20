<?php

namespace App\Actions;

use App\DTO\FlashSaleRequestData;
use App\Models\FlashSaleRequest;

class CreateFlashSaleRequestAction
{
    public function execute(FlashSaleRequestData $data): FlashSaleRequest
    {
        $request = FlashSaleRequest::create($data->toArray());
        if (! empty($data->requested_product_ids)) {
            $request->products()->attach($data->requested_product_ids);
        }

        return $request;
    }
}
