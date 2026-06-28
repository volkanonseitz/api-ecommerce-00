<?php

declare(strict_types=1);

namespace App\Modules\Address\Actions;

use App\Models\Address;

final class DeleteAddressAction
{
    public function execute(Address $address): void
    {
        $address->delete();
    }
}
