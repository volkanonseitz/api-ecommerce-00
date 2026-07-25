<?php

namespace App\Listeners;

use App\Enums\Permission;
use App\Models\User;
use App\Modules\Order\Events\OrderDelivered;
use App\Notifications\OrderDeliveredNotification; // Event yang sudah direstrukturisasi
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// Untuk debugging jika diperlukan

class SendOrderDeliveredNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderDelivered $event): void
    {
        $order = $event->order;

        // Mengirim notifikasi ke customer
        if ($order->customer) {
            $order->customer->notify(new OrderDeliveredNotification($order));
        }

        // Mengirim notifikasi ke vendor
        if ($order->children) {
            foreach ($order->children as $childOrder) {
                if ($childOrder->shop && $childOrder->shop->owner_id) {
                    $vendor = User::find($childOrder->shop->owner_id);
                    if ($vendor) {
                        $vendor->notify(new OrderDeliveredNotification($order));
                    }
                }
            }
        }

        // Mengirim notifikasi ke super admin (opsional)
        $superAdmins = User::whereHas('permissions', fn ($query) => $query->where('name', Permission::SUPER_ADMIN->value))->get();
        foreach ($superAdmins as $admin) {
            $admin->notify(new OrderDeliveredNotification($order));
        }

        // Logika SMS ditiadakan untuk saat ini
    }
}
