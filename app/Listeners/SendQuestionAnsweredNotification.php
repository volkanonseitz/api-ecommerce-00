<?php

namespace App\Listeners;

use App\Enums\Permission;
use App\Models\User;
use App\Modules\Message\Events\QuestionAnswered;
use App\Notifications\NotifyQuestionAnswered; // Event ini tetap di App\Events sesuai rekomendasi
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// Untuk debugging jika diperlukan

class SendQuestionAnsweredNotification implements ShouldQueue
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
    public function handle(QuestionAnswered $event): void
    {
        $question = $event->question;

        // Mengirim notifikasi ke user yang bertanya
        if ($question->user) {
            $question->user->notify(new NotifyQuestionAnswered($question));
        }

        // Mengirim notifikasi ke pemilik toko (jika pertanyaan terkait produk toko)
        if ($question->product && $question->product->shop && $question->product->shop->owner) {
            $question->product->shop->owner->notify(new NotifyQuestionAnswered($question));
        }

        // Mengirim notifikasi ke super admin (opsional)
        $superAdmins = User::whereHas('permissions', fn ($query) => $query->where('name', Permission::SUPER_ADMIN->value))->get();
        foreach ($superAdmins as $admin) {
            $admin->notify(new NotifyQuestionAnswered($question));
        }

        // Logika SMS ditiadakan untuk saat ini
    }
}
