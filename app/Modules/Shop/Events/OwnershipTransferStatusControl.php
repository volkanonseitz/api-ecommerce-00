<?php

namespace App\Modules\Shop\Events;

use App\Models\OwnershipTransfer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OwnershipTransferStatusControl
{
    use Dispatchable, SerializesModels;

    public OwnershipTransfer $ownershipTransfer;

    public function __construct(OwnershipTransfer $ownershipTransfer)
    {
        $this->ownershipTransfer = $ownershipTransfer;
    }
}
