<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class MaintenanceReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $settings; // Atau object settings jika ada model Settings

    /**
     * Create a new notification instance.
     */
    public function __construct(array $settings) // Asumsi menerima array settings
    {
        $this->settings = $settings;
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
        // Asumsi bahasa ada di settings atau fallback ke app.locale
        $translationEnabled = config('shop.translation_enabled', false);
        $defaultLanguage = config('shop.default_language', 'en');
        $locale = $defaultLanguage;

        if ($translationEnabled && isset($this->settings['language'])) { // Asumsi language ada di array settings
            $locale = $this->settings['language'];
        } else {
            // Fallback to config('app.locale') if translation not enabled or language not in settings
            $locale = config('app.locale');
        }
        App::setLocale($locale);

        try {
            $subject = __('maintenance.reminder_subject');
            $url = config('shop.shop_url').'/admin/settings';
        } catch (\Exception $e) {
            Log::error('Configuration missing for MaintenanceReminder: '.$e->getMessage());
            $subject = 'Maintenance Reminder';
            $url = url('/admin');
        }

        return (new MailMessage)
            ->subject($subject)
            ->markdown(
                'emails.maintenance.reminder',
                [
                    'settings' => $this->settings,
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
