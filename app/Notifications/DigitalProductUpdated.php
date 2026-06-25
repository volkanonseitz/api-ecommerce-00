<?php

namespace App\Notifications;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DigitalProductUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private User $customer,
        private Product $product,
        private array $optionalData
    ) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Digital Product Updated: '.$this->product->name)
            ->line('A digital product you purchased has been updated.')
            ->line($this->optionalData['update_message'] ?? 'Please check the updated content.')
            ->action('View Product', url('/products/'.$this->product->slug))
            ->line('Thank you for using our application!');
    }
}
