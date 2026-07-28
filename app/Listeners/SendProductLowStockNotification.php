<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\Permission;
use App\Models\User;
use App\Modules\Shop\Events\ProductLowStock;
use App\Notifications\ProductLowStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendProductLowStockNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(ProductLowStock $event): void
    {
        // Notify shop owner
        if ($event->product->shop && $event->product->shop->owner) {
            $event->product->shop->owner->notify(new ProductLowStockNotification($event->product));
        }

        // Notify super admins
        $superAdmins = User::permission(Permission::SUPER_ADMIN->value)->get();
        foreach ($superAdmins as $admin) {
            $admin->notify(new ProductLowStockNotification($event->product));
        }
    }
}
