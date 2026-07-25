<?php

namespace App\Listeners;

use App\Enums\Permission; // Asumsi enum Permission ada
use App\Models\Order;
use App\Models\User;
use App\Modules\Payment\Events\PaymentSuccess;
use App\Notifications\PaymentSuccessfulNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// Untuk debugging jika diperlukan

class SendPaymentSuccessfulNotification implements ShouldQueue
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
    public function handle(PaymentSuccess $event): void
    {
        // Temukan order object dari event
        $order = $event->order;

        // Mengirim notifikasi ke vendor
        if ($order->children) {
            foreach ($order->children as $childOrder) {
                // Asumsi $childOrder memiliki relasi ke shop, dan shop punya owner_id
                if ($childOrder->shop && $childOrder->shop->owner_id) {
                    $vendor = User::find($childOrder->shop->owner_id);
                    if ($vendor) {
                        $vendor->notify(new PaymentSuccessfulNotification($order));
                    }
                }
            }
        }

        // Mengirim notifikasi ke customer
        if ($order->customer) {
            $order->customer->notify(new PaymentSuccessfulNotification($order));
        }

        // Mengirim notifikasi ke super admin (opsional, berdasarkan kebutuhan)
        $superAdmins = User::whereHas('permissions', fn ($query) => $query->where('name', Permission::SUPER_ADMIN->value))->get();
        foreach ($superAdmins as $admin) {
            $admin->notify(new PaymentSuccessfulNotification($order));
        }

        // Logika SMS ditiadakan untuk saat ini karena belum ada implementasi SmsTrait yang diverifikasi
        // Jika dibutuhkan, Anda perlu mengimplementasikan trait SMS secara terpisah
    }
}
