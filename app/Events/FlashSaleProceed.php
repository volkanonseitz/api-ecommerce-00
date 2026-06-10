<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Database\Models\FlashSale;
use App\Database\Models\Order;

class FlashSaleProcessed
{
    use Dispatchable, SerializesModels;

    public string $action;
    public string $language;
    public $optional_data;

    public function __construct($action, $language = null, $optional_data = null)
    {
        $this->action = $action;
        $this->language = $language;
        $this->optional_data = $optional_data;
    }
}