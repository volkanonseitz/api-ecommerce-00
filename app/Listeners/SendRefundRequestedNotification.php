<?php

namespace App\Listeners;

use App\Enums\Permission;
use App\Models\User;
use App\Modules\Refund\Events\RefundRequested;
use App\Notifications\RefundRequestedNotification; // Event yang sudah direstrukturisasi
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// Untuk debugging jika diperlukan

class SendRefundRequestedNotification implements ShouldQueue
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
    public function handle(RefundRequested $event): void
    {
        $refund = $event->refund;

        // Mengirim notifikasi ke customer
        if ($refund->customer) {
            $refund->customer->notify(new RefundRequestedNotification($refund));
        }

        // Mengirim notifikasi ke admin
        $superAdmins = User::whereHas('permissions', fn ($query) => $query->where('name', Permission::SUPER_ADMIN->value))->get();
        foreach ($superAdmins as $admin) {
            $admin->notify(new RefundRequestedNotification($refund));
        }

        // Jika Anda ingin mengirim notifikasi ke pemilik toko yang terkait dengan order ini
        // if ($refund->order && $refund->order->shop && $refund->order->shop->owner) {
        //     $refund->order->shop->owner->notify(new RefundRequestedNotification($refund));
        // }

        // Logika SMS ditiadakan untuk saat ini
    }
}
