<?php

namespace App\Actions;

use App\DTO\FlashSaleRequestData;
use App\Models\FlashSaleRequest;

class UpdateFlashSaleRequestAction
{
    public function execute(FlashSaleRequest $request, FlashSaleRequestData $data): FlashSaleRequest
    {
        $request->update($data->toArray());
        if (! empty($data->requested_product_ids)) {
            $request->products()->sync($data->requested_product_ids);
        }

        return $request->fresh();
    }
}
