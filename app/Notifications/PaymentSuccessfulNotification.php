<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App; // Jika ingin menggunakan setLocale
use Illuminate\Support\Facades\Log; // Untuk debugging jika diperlukan

class PaymentSuccessfulNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $translationEnabled = config('shop.translation_enabled', false);
        $defaultLanguage = config('shop.default_language', 'en');
        $locale = $defaultLanguage;

        if ($translationEnabled && isset($this->order->language)) {
            $locale = $this->order->language;
        }
        App::setLocale($locale);

        try {
            $url = config('shop.shop_url').'/orders/'.$this->order->tracking_number;
        } catch (\Exception $e) {
            Log::error('shop.shop_url not configured: '.$e->getMessage());
            $url = url('/orders/'.$this->order->tracking_number); // Fallback URL
        }

        return (new MailMessage)
            ->subject(__('payment.success_subject', ['ORDER_TRACKING_NUMBER' => $this->order->tracking_number]))
            ->markdown(
                'emails.payment.payment-successful',
                [
                    'order' => $this->order,
                    'url' => $url,
                ]
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
