<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ProductLowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Product $product
    ) {}

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
        return (new MailMessage)
            ->subject('Peringatan Stok Rendah: '.$this->product->name)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Produk "'.$this->product->name.'" Anda memiliki stok rendah.')
            ->line('Stok tersedia: '.$this->product->available_quantity)
            ->line('Ambang batas stok rendah: '.$this->product->low_stock_threshold)
            ->action('Lihat Produk', url('/products/'.$this->product->slug))
            ->line('Mohon segera lakukan restock untuk menghindari kehabisan stok.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'available_quantity' => $this->product->available_quantity,
            'low_stock_threshold' => $this->product->low_stock_threshold,
            'message' => 'Product is running low on stock.',
        ];
    }
}
