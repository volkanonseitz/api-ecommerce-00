<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Database\Models\FlashSale;
use App\Database\Models\Order;

class Maintenance
{
    use Dispatchable, SerializesModels;

    public $language;

    public function __construct(string $language)
    {
        $this->language = $language;
    }
}