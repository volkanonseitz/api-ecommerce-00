<?php

namespace App\Mail;

use App\Models\Balance;
use App\Models\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

// Tambahkan ini

class AdminCommissionRateUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public Shop $shop;

    public Balance $balance;

    /**
     * Create a new message instance.
     */
    public function __construct(Shop $shop, Balance $balance)
    {
        $this->shop = $shop;
        $this->balance = $balance;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $translationEnabled = config('shop.translation_enabled', false);
        $defaultLanguage = config('shop.default_language', 'en');
        $locale = $defaultLanguage;

        if ($translationEnabled && isset($this->shop->language)) { // Asumsi shop punya kolom language
            $locale = $this->shop->language;
        } else {
            $locale = config('app.locale');
        }
        App::setLocale($locale);

        return new Envelope(
            subject: __('commission.admin_update_subject', ['SHOP_NAME' => $this->shop->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin-commission-rate-update',
            with: [
                'shopName' => $this->shop->name,
                'totalEarnings' => $this->balance->total_earnings,
                'currentBalance' => $this->balance->current_balance,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
