<?php

namespace App\Notifications;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OwnershipTransferredStatus extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Shop $shop,
        private User $previousOwner,
        private User $newOwner,
        private ?array $optional = null
    ) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = config('shop.dashboard_url')."/{$this->shop->slug}";
        $message = $this->optional['message'] ?? '';

        return (new MailMessage)
            ->subject(config('shop.app_notice_domain').' Shop Ownership Reminder')
            ->markdown('emails.ownership.status', [
                'shopName' => $this->shop->name,
                'newOwnerName' => $this->newOwner->name,
                'previousOwnerName' => $this->previousOwner->name,
                'url' => $url,
                'message' => $message,
            ]);
    }
}
