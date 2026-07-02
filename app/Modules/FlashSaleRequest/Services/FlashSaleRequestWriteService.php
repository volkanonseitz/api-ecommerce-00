<?php

declare(strict_types=1);

namespace App\Modules\FlashSaleRequest\Services;

use App\Models\FlashSaleRequest;
use App\Modules\FlashSaleRequest\Actions\CreateFlashSaleRequestAction;
use App\Modules\FlashSaleRequest\Actions\UpdateFlashSaleRequestAction;
use App\Modules\FlashSaleRequest\DTO\FlashSaleRequestData;

final class FlashSaleRequestWriteService
{
    public function __construct(
        private readonly CreateFlashSaleRequestAction $createAction,
        private readonly UpdateFlashSaleRequestAction $updateAction,
    ) {}

    public function create(FlashSaleRequestData $data): FlashSaleRequest
    {
        return $this->createAction->execute($data);
    }

    public function update(FlashSaleRequest $request, FlashSaleRequestData $data): FlashSaleRequest
    {
        return $this->updateAction->execute($request, $data);
    }
}
