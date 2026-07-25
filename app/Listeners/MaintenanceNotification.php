<?php

namespace App\Listeners;

use App\Enums\Permission;
use App\Events\Maintenance;
use App\Models\Settings;
use App\Models\User;
use App\Notifications\MaintenanceReminder; // Asumsi model Settings ada
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class MaintenanceNotification implements ShouldQueue
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
    public function handle(Maintenance $event): void
    {
        try {
            // Ambil pengaturan maintenance dari database
            $settings = Settings::where('language', $event->language)
                ->firstWhere('key', 'maintenance') // Asumsi key 'maintenance'
                ->value ?? []; // Asumsi value adalah array

            // Cek apakah mode maintenance aktif
            $isUnderMaintenance = $settings['isUnderMaintenance'] ?? false;
            $shouldSendEmail = $settings['shouldSendEmail'] ?? false; // Flag untuk kirim email
            $startTime = Carbon::parse($settings['start'] ?? null);
            $untilTime = Carbon::parse($settings['until'] ?? null);
            $currentTime = Carbon::now();

            // Kirim email hanya jika mode maintenance aktif dan sesuai jadwal
            if ($isUnderMaintenance && $shouldSendEmail && $currentTime->between($startTime, $untilTime)) {
                $adminUsers = User::whereHas('permissions', fn ($query) => $query->where('name', Permission::SUPER_ADMIN->value))->get();
                foreach ($adminUsers as $admin) {
                    $admin->notify(new MaintenanceReminder($settings));
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in MaintenanceNotification Listener: '.$e->getMessage(), ['event' => $event]);
        }
    }
}
