<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Product $product) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Product Approved: '.$this->product->name)
            ->line('Your product has been approved.')
            ->action('View Product', url('/products/'.$this->product->slug))
            ->line('Thank you for using our application!');
    }
}
