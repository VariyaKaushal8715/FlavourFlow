<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'whatsapp' => [
        'enabled' => env('WHATSAPP_ENABLED', true),
        'token' => env('WHATSAPP_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'api_version' => env('WHATSAPP_API_VERSION', 'v21.0'),
        'log_only' => env('WHATSAPP_LOG_ONLY', false),
    ],

    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID', 'rzp_test_dummy_key_id'),
        'key_secret' => env('RAZORPAY_KEY_SECRET', 'rzp_test_dummy_key_secret'),
        'mode' => env('RAZORPAY_MODE', 'test'),
    ],

    'cashfree' => [
        'app_id' => env('CASHFREE_APP_ID'),
        'secret_key' => env('CASHFREE_SECRET_KEY'),
        'api_version' => env('CASHFREE_API_VERSION', '2023-08-01'),
        'env' => env('CASHFREE_ENV', 'PRODUCTION'),
        'return_url' => env('CASHFREE_RETURN_URL'),
        'notify_url' => env('CASHFREE_NOTIFY_URL'),
    ],

];
