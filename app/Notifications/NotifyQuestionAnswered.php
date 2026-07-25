<?php

namespace App\Notifications;

use App\Models\Question; // Asumsi model Question ada
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class NotifyQuestionAnswered extends Notification implements ShouldQueue
{
    use Queueable;

    protected Question $question;

    /**
     * Create a new notification instance.
     */
    public function __construct(Question $question)
    {
        $this->question = $question;
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
        // Asumsi question memiliki product yang memiliki bahasa
        // atau fallback ke bahasa aplikasi
        $translationEnabled = config('shop.translation_enabled', false);
        $defaultLanguage = config('shop.default_language', 'en');
        $locale = $defaultLanguage;

        if ($translationEnabled && isset($this->question->product->language)) {
            $locale = $this->question->product->language;
        }
        App::setLocale($locale);

        try {
            $url = config('shop.shop_url').'/products/'.$this->question->product->id;
        } catch (\Exception $e) {
            Log::error('shop.shop_url not configured: '.$e->getMessage());
            $url = url('/products/'.$this->question->product->id); // Fallback URL
        }

        return (new MailMessage)
            ->subject(__('question.answered_subject', ['PRODUCT_NAME' => $this->question->product->name]))
            ->markdown(
                'emails.question.answered',
                [
                    'question' => $this->question,
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
