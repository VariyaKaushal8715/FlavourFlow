<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Store / Seller Contact Information
    |--------------------------------------------------------------------------
    |
    | Centralized store and seller contact information used in order
    | notifications, receipts, emails, and WhatsApp messages.
    |
    */
    'store' => [
        'name' => env('STORE_NAME', config('personal_site.brand.name', 'FlavourFlow')),
        'phone' => env('STORE_PHONE', config('personal_site.contact.phone', '+91 99999 99999')),
        'email' => env('STORE_EMAIL', config('personal_site.contact.email', 'support@flavourflow.com')),
        'whatsapp' => env('STORE_WHATSAPP', config('personal_site.contact.whatsapp', '+91 99999 99999')),
        'address' => env('STORE_ADDRESS', config('personal_site.contact.address', 'Patan, Gujarat, India')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin / Seller Notification Recipients
    |--------------------------------------------------------------------------
    |
    | Contact information for the store administrator / seller to receive
    | new order alerts.
    |
    */
    'admin' => [
        'email' => env('ADMIN_NOTIFICATION_EMAIL', env('ADMIN_EMAIL', 'admin@flavourflow.test')),
        'whatsapp' => env('ADMIN_NOTIFICATION_WHATSAPP', env('ADMIN_WHATSAPP', '+91 99999 99999')),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for sending WhatsApp notifications via different providers.
    | Supported providers: 'log', 'mock', 'twilio', 'meta', 'custom'
    |
    */
    'whatsapp' => [
        'enabled' => env('WHATSAPP_NOTIFICATIONS_ENABLED', true),
        'provider' => env('WHATSAPP_PROVIDER', 'log'),
        'timeout' => env('WHATSAPP_TIMEOUT', 10),

        'twilio' => [
            'sid' => env('TWILIO_ACCOUNT_SID'),
            'token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_WHATSAPP_FROM'), // e.g. 'whatsapp:+14155238886'
        ],

        'meta' => [
            'api_url' => env('META_WHATSAPP_API_URL', 'https://graph.facebook.com/v19.0'),
            'phone_number_id' => env('META_WHATSAPP_PHONE_NUMBER_ID'),
            'access_token' => env('META_WHATSAPP_ACCESS_TOKEN'),
        ],

        'custom' => [
            'api_url' => env('CUSTOM_WHATSAPP_API_URL'),
            'api_key' => env('CUSTOM_WHATSAPP_API_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Notifications Configuration
    |--------------------------------------------------------------------------
    */
    'email' => [
        'enabled' => env('EMAIL_NOTIFICATIONS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Active Notification Channels
    |--------------------------------------------------------------------------
    */
    'channels' => [
        'customer_whatsapp' => env('NOTIFY_CUSTOMER_WHATSAPP', true),
        'customer_email' => env('NOTIFY_CUSTOMER_EMAIL', true),
        'admin_whatsapp' => env('NOTIFY_ADMIN_WHATSAPP', true),
        'admin_email' => env('NOTIFY_ADMIN_EMAIL', true),
    ],
];
