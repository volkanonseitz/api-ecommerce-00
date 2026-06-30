<?php

declare(strict_types=1);

namespace App\Modules\FlashSaleRequest\Actions;

use App\Models\FlashSaleRequest;
use App\Modules\FlashSaleRequest\DTO\FlashSaleRequestData;

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
