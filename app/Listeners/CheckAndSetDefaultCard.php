<?php

namespace App\Listeners;

use App\Events\PaymentMethods;
use App\Models\PaymentMethod; // Asumsi model PaymentMethod ada
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CheckAndSetDefaultCard implements ShouldQueue
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
    public function handle(PaymentMethods $event): void
    {
        $currentPaymentMethod = $event->payment_methods; // Ini adalah PaymentMethod yang baru dibuat/diperbarui

        // Hanya proses jika payment method memiliki user_id
        if (! $currentPaymentMethod->user_id) {
            return;
        }

        // Jika metode pembayaran yang baru adalah default, pastikan metode lain bukan default
        if ($currentPaymentMethod->default_card) {
            PaymentMethod::where('user_id', $currentPaymentMethod->user_id)
                ->where('id', '!=', $currentPaymentMethod->id)
                ->update(['default_card' => false]);
            Log::info("Payment method {$currentPaymentMethod->id} set as default for user {$currentPaymentMethod->user_id}. Other methods unset.");
        } else {
            // Jika tidak ada metode pembayaran default yang ditetapkan untuk user ini,
            // dan ini adalah satu-satunya metode pembayaran, jadikan ini default.
            $hasDefault = PaymentMethod::where('user_id', $currentPaymentMethod->user_id)
                ->where('default_card', true)
                ->exists();
            if (! $hasDefault && PaymentMethod::where('user_id', $currentPaymentMethod->user_id)->count() === 1) {
                $currentPaymentMethod->default_card = true;
                $currentPaymentMethod->save();
                Log::info("Payment method {$currentPaymentMethod->id} automatically set as default for user {$currentPaymentMethod->user_id}.");
            }
        }
    }
}
