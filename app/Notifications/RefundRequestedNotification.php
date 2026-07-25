<?php

namespace App\Notifications;

use App\Models\Refund; // Asumsi model Refund ada
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class RefundRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Refund $refund;

    /**
     * Create a new notification instance.
     */
    public function __construct(Refund $refund)
    {
        $this->refund = $refund;
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
        // Asumsi refund memiliki order yang memiliki bahasa
        $translationEnabled = config('shop.translation_enabled', false);
        $defaultLanguage = config('shop.default_language', 'en');
        $locale = $defaultLanguage;

        if ($translationEnabled && isset($this->refund->order->language)) {
            $locale = $this->refund->order->language;
        }
        App::setLocale($locale);

        try {
            $url = config('shop.shop_url').'/refunds/'.$this->refund->id;
        } catch (\Exception $e) {
            Log::error('shop.shop_url not configured: '.$e->getMessage());
            $url = url('/refunds/'.$this->refund->id); // Fallback URL
        }

        return (new MailMessage)
            ->subject(__('refund.requested_subject', ['REFUND_ID' => $this->refund->id]))
            ->markdown(
                'emails.refund.requested',
                [
                    'refund' => $this->refund,
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
