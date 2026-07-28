<?php

namespace App\Listeners;

use App\Enums\Permission;
use App\Models\User;
use App\Modules\Review\Events\ReviewCreated;
use App\Notifications\NewReviewCreated; // Event ini tetap di App\Events sesuai rekomendasi
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// Untuk debugging jika diperlukan

class SendReviewNotification implements ShouldQueue
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
    public function handle(ReviewCreated $event): void
    {
        $review = $event->review;

        // Mengirim notifikasi ke pemilik toko (jika ulasan terkait produk toko)
        if ($review->product && $review->product->shop && $review->product->shop->owner) {
            $review->product->shop->owner->notify(new NewReviewCreated($review));
        }

        // Mengirim notifikasi ke super admin (opsional)
        $superAdmins = User::whereHas('permissions', fn ($query) => $query->where('name', Permission::SUPER_ADMIN->value))->get();
        foreach ($superAdmins as $admin) {
            $admin->notify(new NewReviewCreated($review));
        }

        // Logika SMS ditiadakan untuk saat ini
    }
}
