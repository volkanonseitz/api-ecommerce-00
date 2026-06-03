<?php

return [
    'dashboard_url' => env('DASHBOARD_URL', 'https://dashboard.example.com'),
    'shop_url' => env('SHOP_URL', 'https://shop.example.com'),
    'app_notice_domain' => env('APP_NOTICE_DOMAIN', 'APP_'),
    'admin_email' => env('ADMIN_EMAIL', 'support@example.com'),

    'default_language' => env('DEFAULT_LANGUAGE', 'id'),
    'translation_enabled' => env('TRANSLATION_ENABLED', true),
    'default_currency' => env('DEFAULT_CURRENCY', 'IDR'),
    'active_payment_gateway' => env('ACTIVE_PAYMENT_GATEWAY', 'stripe'),

    'dummy_data_path' => env('DUMMY_DATA_PATH', storage_path('dummy')),
];