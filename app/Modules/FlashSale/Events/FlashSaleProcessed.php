<?php

namespace App\Modules\FlashSale\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FlashSaleProcessed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $action,
        public ?string $language = null,
        public mixed $optional_data = null,
    ) {}
}
