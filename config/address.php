<?php
return [
    // Enable India‑only delivery restriction (toggle via .env)
    'india_only' => env('INDIA_ONLY', true),

    // Regex for a valid 6‑digit Indian PIN code
    'pincode_regex' => '/^[1-9][0-9]{5}$/',

    // Path to static PIN → location mapping (fallback when no external provider)
    'pincode_dataset' => resource_path('address/pincode_data.json'),

    // Optional external geocode provider class (must implement GeocodeProviderInterface)
    'geocode_provider' => env('GEOCODE_PROVIDER', null),
];

