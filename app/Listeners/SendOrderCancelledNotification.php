<?php

namespace App\Listeners;

use App\Enums\Permission;
use App\Models\User;
use App\Modules\Order\Events\OrderCancelled;
use App\Notifications\OrderCancelledNotification; // Event yang sudah direstrukturisasi
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// Untuk debugging jika diperlukan

class SendOrderCancelledNotification implements ShouldQueue
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
    public function handle(OrderCancelled $event): void
    {
        $order = $event->order;

        // Mengirim notifikasi ke customer
        if ($order->customer) {
            $order->customer->notify(new OrderCancelledNotification($order));
        }

        // Mengirim notifikasi ke vendor
        if ($order->children) {
            foreach ($order->children as $childOrder) {
                if ($childOrder->shop && $childOrder->shop->owner_id) {
                    $vendor = User::find($childOrder->shop->owner_id);
                    if ($vendor) {
                        $vendor->notify(new OrderCancelledNotification($order));
                    }
                }
            }
        }

        // Mengirim notifikasi ke super admin (opsional)
        $superAdmins = User::whereHas('permissions', fn ($query) => $query->where('name', Permission::SUPER_ADMIN->value))->get();
        foreach ($superAdmins as $admin) {
            $admin->notify(new OrderCancelledNotification($order));
        }

        // Logika SMS ditiadakan untuk saat ini
    }
}
