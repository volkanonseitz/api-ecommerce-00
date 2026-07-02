<?php

namespace App\Modules\Settings\Services;

use App\Models\Settings;

class NotificationRecipientService
{
    public function getWhichUserWillGetEmail(string $eventType, string $language): array
    {
        // Logika menentukan siapa yang akan menerima email berdasarkan settings
        // Contoh sederhana: always notify vendor and admin
        $settings = Settings::getData($language);
        $vendorEnabled = $settings->options['notify_vendor'] ?? true;
        $adminEnabled = $settings->options['notify_admin'] ?? true;

        return [
            'vendor' => $vendorEnabled,
            'admin' => $adminEnabled,
        ];
    }
}
