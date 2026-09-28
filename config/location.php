<?php

return [
    // Feature flag to enable/disable India‑only address restriction
    'india_only' => env('INDIA_ONLY', true),

    // List of Indian states and union territories
    'states' => [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
        'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
        'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
        'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 'Rajasthan',
        'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh',
        'Uttarakhand', 'West Bengal', 'Andaman and Nicobar Islands',
        'Chandigarh', 'Dadra and Nagar Haveli and Daman and Diu', 'Delhi',
        'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry',
    ],

    // Simple regex for a 6‑digit Indian PIN code
    'pincode_regex' => '/^[1-9][0-9]{5}$/',
    // Mapping of state to allowed PIN code prefixes (first two digits) for simple mismatch validation
    'pincode_state_prefixes' => [
        // Example mappings – extend as needed
        'Gujarat' => ['38'],
        'Delhi' => ['11'],
        'Maharashtra' => ['40', '41', '42', '43', '44'],
        // Add other states as required
    ],
];
