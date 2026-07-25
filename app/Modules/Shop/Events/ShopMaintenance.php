<?php

namespace App\Modules\Shop\Events;

use App\Models\Shop;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShopMaintenance
{
    use Dispatchable, SerializesModels;

    public Shop $shop;

    public string $action;

    /**
     * Create a new event instance.
     *
     * @param  string  $action  (enable | disable | start)
     */
    public function __construct(Shop $shop, string $action)
    {
        $this->shop = $shop;
        $this->action = $action;
    }
}
