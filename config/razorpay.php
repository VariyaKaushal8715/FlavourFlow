<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Razorpay API Credentials
    |--------------------------------------------------------------------------
    |
    | These values are read from environment variables and must never be
    | hard-coded. Use RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET in your
    | .env file. In test mode, use the Razorpay Test-Mode key pair.
    |
    */

    'key_id' => env('RAZORPAY_KEY_ID', ''),
    'key_secret' => env('RAZORPAY_KEY_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | The secret used to verify incoming Razorpay webhook signatures.
    | Set this in Razorpay Dashboard → Webhooks → Secret.
    |
    */

    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */

    'currency' => 'INR',

];
