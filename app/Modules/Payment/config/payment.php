<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for various payment gateways.
    | Each gateway can be enabled/disabled and configured independently.
    |
    */
    
    'default_gateway' => config('PAYMENT_DEFAULT_GATEWAY', 'stripe'),
    
    'gateways' => [
        'stripe' => [
            'enabled' => config('STRIPE_ENABLED', true),
            'secret_key' => config('STRIPE_SECRET_KEY'),
            'public_key' => config('STRIPE_PUBLIC_KEY'),
            'webhook_secret' => config('STRIPE_WEBHOOK_SECRET'),
            'currencies' => ['usd', 'eur', 'gbp', 'aud', 'cad'],
        ],
        
        'midtrans' => [
            'enabled' => config('MIDTRANS_ENABLED', false),
            'server_key' => config('MIDTRANS_SERVER_KEY'),
            'client_key' => config('MIDTRANS_CLIENT_KEY'),
            'is_production' => config('MIDTRANS_IS_PRODUCTION', false),
            'currencies' => ['idr'],
        ],
        
        'xendit' => [
            'enabled' => config('XENDIT_ENABLED', false),
            'secret_key' => config('XENDIT_SECRET_KEY'),
            'public_key' => config('XENDIT_PUBLIC_KEY'),
            'currencies' => ['idr', 'php', 'thb', 'vnd'],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Payment Method Types
    |--------------------------------------------------------------------------
    |
    | Supported payment method types across all gateways.
    | Gateway-specific implementations may support subsets of these.
    |
    */
    'supported_method_types' => [
        'card',
        'virtual_account',
        'qris',
        'ewallet',
        'direct_debit',
        'bank_transfer',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for payment-related data.
    |
    */
    'cache' => [
        'gateway_list_ttl' => 86400, // 24 hours
        'payment_methods_ttl' => 3600, // 1 hour
        'supported_methods_ttl' => 7200, // 2 hours
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related configuration for payment processing.
    |
    */
    'security' => [
        'max_payment_amount' => config('PAYMENT_MAX_AMOUNT', 1000000), // 1,000,000 in base currency
        'min_payment_amount' => config('PAYMENT_MIN_AMOUNT', 1), // 1 in base currency
        'allow_saved_cards' => config('PAYMENT_ALLOW_SAVED_CARDS', true),
        'require_cvv_for_saved_cards' => config('PAYMENT_REQUIRE_CVV_FOR_SAVED_CARDS', true),
        'payment_timeout' => config('PAYMENT_TIMEOUT', 1800), // 30 minutes in seconds
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    |
    | Webhook configuration for payment notifications.
    |
    */
    'webhooks' => [
        'enabled' => config('PAYMENT_WEBHOOKS_ENABLED', true),
        'queue' => config('PAYMENT_WEBHOOK_QUEUE', 'default'),
        'max_retries' => config('PAYMENT_WEBHOOK_MAX_RETRIES', 3),
        'retry_delay' => config('PAYMENT_WEBHOOK_RETRY_DELAY', 60), // 1 minute
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    |
    | Logging configuration for payment operations.
    |
    */
    'logging' => [
        'enabled' => config('PAYMENT_LOGGING_ENABLED', true),
        'channel' => config('PAYMENT_LOG_CHANNEL', 'stack'),
        'level' => config('PAYMENT_LOG_LEVEL', 'info'),
        'mask_sensitive_data' => config('PAYMENT_MASK_SENSITIVE_DATA', true),
    ],
];