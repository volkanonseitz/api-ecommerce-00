<?php

namespace App\Services;

use App\Models\Shipping;
use App\DTO\ShippingData;
use App\Actions\CreateShippingAction;
use App\Actions\UpdateShippingAction;

class ShippingService
{
    public function __construct(
        private CreateShippingAction $createAction,
        private UpdateShippingAction $updateAction,
    ) {}

    public function getAll()
    {
        return Shipping::all();
    }

    public function findOrFail(int $id): Shipping
    {
        return Shipping::findOrFail($id);
    }

    public function create(ShippingData $data): Shipping
    {
        return $this->createAction->execute($data);
    }

    public function update(Shipping $shipping, ShippingData $data): Shipping
    {
        return $this->updateAction->execute($shipping, $data);
    }

    public function delete(Shipping $shipping): void
    {
        $shipping->delete();
    }
}