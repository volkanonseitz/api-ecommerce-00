<?php

namespace App\Events;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProcessOwnershipTransition
{
    use Dispatchable, SerializesModels;

    public Shop $shop;

    public User $previousOwner;

    public User $newOwner;

    public ?array $optional;

    /**
     * Create a new event instance.
     */
    public function __construct(Shop $shop, User $previousOwner, User $newOwner, ?array $optional = null)
    {
        $this->shop = $shop;
        $this->previousOwner = $previousOwner;
        $this->newOwner = $newOwner;
        $this->optional = $optional;
    }
}
