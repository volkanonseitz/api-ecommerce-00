<?php

namespace App\Listeners;

use App\Enums\Permission;
use App\Mail\AdminCommissionRateUpdate;
use App\Mail\VendorCommissionRateUpdate;
use App\Models\User;
use App\Modules\Shop\Events\CommissionRateUpdateEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class CommissionRateUpdateListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
        //
    }

    public function handle(CommissionRateUpdateEvent $event)
    {
        $shop = $event->shop;
        $balance = $event->balance;

        // Kirim email ke admin
        $adminUsers = User::whereHas('permissions', function ($query) {
            $query->where('name', Permission::SUPER_ADMIN->value);
        })->get();

        foreach ($adminUsers as $admin) {
            Mail::to($admin->email)->send(new AdminCommissionRateUpdate($shop, $balance));
        }

        // Kirim email ke pemilik toko
        if ($shop->owner && $shop->owner->email) {
            Mail::to($shop->owner->email)->send(new VendorCommissionRateUpdate($shop, $balance));
        }
    }
}
