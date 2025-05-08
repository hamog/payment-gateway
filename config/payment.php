<?php


return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment gateway that will be used for
    | payment processing when no gateway is specified.
    |
    */
    'default_gateway' => env('PAYMENT_GATEWAY', 'stripe'),

    /*
    |--------------------------------------------------------------------------
    | Available Payment Gateways
    |--------------------------------------------------------------------------
    |
    | This array maps gateway identifiers to their implementation classes.
    | To add a new payment gateway, simply add it to this array with a
    | unique identifier as the key and the gateway class as the value.
    |
    */
    'gateways' => [
        'stripe' => \Hamog\Payment\Gateways\StripeGateway::class,
        'paypal' => \Hamog\Payment\Gateways\PayPalGateway::class,
        // Add new gateways here as needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configurations
    |--------------------------------------------------------------------------
    |
    | This section contains gateway-specific configuration options.
    | Configuration is primarily stored in services.php and .env files,
    | but you can add additional gateway-specific settings here.
    |
    */
    'settings' => [
        'stripe' => [
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'payment_methods' => ['card', 'sepa_debit', 'ideal'],
        ],
        'paypal' => [
            'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
            'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Logging
    |--------------------------------------------------------------------------
    |
    | Enable or disable logging of payment transactions for debugging
    | and audit purposes.
    |
    */
    'logging' => [
        'enabled' => env('PAYMENT_LOGGING', true),
        'channel' => env('PAYMENT_LOG_CHANNEL', 'stack'),
    ],
];
