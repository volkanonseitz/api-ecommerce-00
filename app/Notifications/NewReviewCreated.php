<?php

namespace App\Notifications;

use App\Models\Review; // Asumsi model Review ada
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class NewReviewCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected Review $review;

    /**
     * Create a new notification instance.
     */
    public function __construct(Review $review)
    {
        $this->review = $review;
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
        // Asumsi review memiliki product yang memiliki bahasa
        // atau fallback ke bahasa aplikasi
        $translationEnabled = config('shop.translation_enabled', false);
        $defaultLanguage = config('shop.default_language', 'en');
        $locale = $defaultLanguage;

        if ($translationEnabled && isset($this->review->product->language)) {
            $locale = $this->review->product->language;
        }
        App::setLocale($locale);

        try {
            $url = config('shop.shop_url').'/products/'.$this->review->product->id;
        } catch (\Exception $e) {
            Log::error('shop.shop_url not configured: '.$e->getMessage());
            $url = url('/products/'.$this->review->product->id); // Fallback URL
        }

        return (new MailMessage)
            ->subject(__('review.created_subject', ['PRODUCT_NAME' => $this->review->product->name]))
            ->markdown(
                'emails.review.created',
                [
                    'review' => $this->review,
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
