<?php

namespace App\Notifications;

use App\Models\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShopMaintenanceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Shop $shop,
        private string $body,
        private string $message
    ) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->message)
            ->line($this->body)
            ->action('View Shop', url('/shops/'.$this->shop->slug))
            ->line('Thank you for using our application!');
    }
}
