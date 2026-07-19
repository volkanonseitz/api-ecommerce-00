<?php

namespace App\Listeners;

use App\Events\ShopMaintenance;
use App\Models\User;
use App\Notifications\ShopMaintenanceNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class ShopMaintenanceListener implements ShouldQueue
{
    public function __construct(private UserService $userService) {}

    public function handle(ShopMaintenance $event)
    {
        $action = $event->action;
        $shop = $event->shop;

        // Dapatkan admin users dari UserService
        $adminUsers = $this->userService->getAdminUsers();

        // Dapatkan owner dan staff shop
        $shopOwnerAndStaff = User::where(function ($query) use ($shop) {
            $query->where('id', $shop->owner_id)
                ->orWhere('shop_id', $shop->id);
        })->get();

        // Gabungkan
        $users = $adminUsers->merge($shopOwnerAndStaff);

        // Ambil tanggal dari settings
        $settings = $shop->settings ?? [];
        $maintenanceSettings = $settings['shopMaintenance'] ?? [];
        $start = Carbon::parse($maintenanceSettings['start'] ?? now())->toDayDateTimeString();
        $until = Carbon::parse($maintenanceSettings['until'] ?? now())->toDayDateTimeString();

        // Tentukan pesan berdasarkan action
        if ($action === 'enable') {
            $message = $shop->name.' shop is going under maintenance';
            $body = "Due to our regular shop maintenance, this shop will be down from $start to $until.";
        } elseif ($action === 'start') {
            $message = $shop->name.' shop maintenance period is started';
            $body = "Due to our regular store maintenance, this store maintenance period has started from $start to $until.";
        } else {
            $message = $shop->name.' shop maintenance period is over';
            $body = "Due to our regular store maintenance, this store maintenance is over from $start to $until.";
        }

        if ($users->isNotEmpty()) {
            foreach ($users as $user) {
                Notification::route('mail', $user->email)
                    ->notify(new ShopMaintenanceNotification($shop, $body, $message));
            }
        }
    }
}
