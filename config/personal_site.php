<?php

return [
    'meta' => [
        'title' => 'FlavourFlow - Pure Indian Spices',
        'description' => 'Fresh, small-batch Indian spices and balanced blends for everyday cooking.',
    ],

    'brand' => [
        'name' => 'FlavourFlow',
        'tagline' => 'Pure spices. Pure love.',
        'logo' => 'images/flavourflow-mark.png',
    ],

    'theme' => [
        'auto_from_logo' => true,
        'primary' => '#b42318',
        'accent' => '#f4b942',
        'ink' => '#09090b',
        'surface' => '#fff9ed',
    ],

    'navigation' => [
        ['label' => 'Home', 'href' => '/#top'],
        ['label' => 'Offers', 'href' => '/#offers'],
        ['label' => 'Products', 'href' => '/#products'],
        ['label' => 'Our story', 'href' => '/#company'],
    ],

    'hero' => [
        'eyebrow' => 'Small-batch Indian spices',
        'title' => 'FlavourFlow',
        'subtitle' => 'Turn everyday cooking into something unforgettable.',
        'description' => 'Bold aroma, honest ingredients, and balanced blends made to bring depth to every plate.',
        'image' => 'images/flavourflow-spice-hero.png',
        'primary_action' => ['label' => 'Explore the collection', 'href' => '#products'],
        'secondary_action' => ['label' => 'View best sellers', 'href' => '#products'],
        'proof_points' => [
            ['value' => '100%', 'label' => 'Pure ingredients'],
            ['value' => '4.9', 'label' => 'Community rating'],
            ['value' => 'Fresh', 'label' => 'Small-batch packed'],
        ],
    ],

    'products' => [
        [
            'name' => 'Royal Garam Masala',
            'category' => 'Signature blend',
            'description' => 'A warm, layered masala built for curries, gravies, biryani, and everyday home cooking.',
            'badge' => 'Hero pick',
            'price' => 'From Rs. 149',
            'compare_at_price' => 'Rs. 179.00',
            'metric' => '4.9 rating',
            'image' => 'images/flavourflow-mark.png',
            'sku' => 'FF-GM-100',
            'unit' => '100 g',
            'quantity' => 120,
            'stock_label' => 'In stock',
            'in_stock' => true,
            'is_featured' => true,
            'is_active' => true,
            'priority' => 95,
            'rating' => 4.9,
        ],
        [
            'name' => 'Red Chilli Powder',
            'category' => 'Pure spice',
            'description' => 'Bright colour, clean heat, and a bold finish for daily recipes.',
            'badge' => 'Best seller',
            'price' => 'From Rs. 99',
            'compare_at_price' => 'Rs. 119.00',
            'metric' => 'High demand',
            'image' => 'images/flavourflow-hero.png',
            'sku' => 'FF-RC-100',
            'unit' => '100 g',
            'quantity' => 86,
            'stock_label' => 'In stock',
            'in_stock' => true,
            'is_featured' => false,
            'is_active' => true,
            'priority' => 88,
            'rating' => 4.8,
        ],
        [
            'name' => 'Kitchen King Mix',
            'category' => 'Everyday blend',
            'description' => 'Balanced spice profile for sabzi, snacks, and quick family meals.',
            'badge' => 'Popular',
            'price' => 'From Rs. 129',
            'compare_at_price' => 'Rs. 149.00',
            'metric' => 'Fast moving',
            'image' => 'images/flavourflow-mark.png',
            'sku' => 'FF-KK-100',
            'unit' => '100 g',
            'quantity' => 48,
            'stock_label' => 'In stock',
            'in_stock' => true,
            'is_featured' => false,
            'is_active' => true,
            'priority' => 82,
            'rating' => 4.7,
        ],
        [
            'name' => 'Turmeric Powder',
            'category' => 'Essential spice',
            'description' => 'Golden colour and earthy aroma for everyday Indian cooking.',
            'badge' => 'Essential',
            'price' => 'From Rs. 79',
            'compare_at_price' => '',
            'metric' => 'Daily use',
            'image' => 'images/flavourflow-mark.png',
            'sku' => 'FF-TP-100',
            'unit' => '100 g',
            'quantity' => 72,
            'stock_label' => 'In stock',
            'in_stock' => true,
            'is_featured' => false,
            'is_active' => true,
            'priority' => 76,
            'rating' => 4.6,
        ],
    ],

    'company' => [
        'eyebrow' => 'The company behind the flavour',
        'title' => 'Built around honest spice and everyday cooking.',
        'description' => 'FlavourFlow started with a simple belief: the spices used every day should be fresh, dependable, and easy to understand. We source thoughtfully, blend in small batches, and keep every pack focused on real kitchen use.',
        'facts' => [
            ['value' => '2021', 'label' => 'Established'],
            ['value' => '25+', 'label' => 'Spices and blends'],
            ['value' => '5,000+', 'label' => 'Orders packed'],
            ['value' => 'Gujarat', 'label' => 'Our roots'],
        ],
        'principles' => [
            [
                'title' => 'Honest sourcing',
                'description' => 'Ingredients are selected for aroma, colour, and consistency without unnecessary fillers.',
            ],
            [
                'title' => 'Small-batch freshness',
                'description' => 'Focused production keeps the flavour lively from the first spoon to the last.',
            ],
            [
                'title' => 'Made for real kitchens',
                'description' => 'Every blend is designed for practical, repeatable everyday cooking.',
            ],
        ],
    ],

    'footer_links' => [
        ['label' => 'Home', 'href' => '/#top'],
        ['label' => 'Offers', 'href' => '/#offers'],
        ['label' => 'Products', 'href' => '/#products'],
        ['label' => 'Our story', 'href' => '/#company'],
    ],

    'footer_location' => [
        'label' => 'Visit our store',
        'name' => 'FlavourFlow Spice House',
        'address_lines' => [
            '12, Aroma Arcade',
            'Navrangpura, Ahmedabad, Gujarat 380009',
            'India',
        ],
        'maps_query' => 'FlavourFlow Spice House, Navrangpura, Ahmedabad, Gujarat',
        'directions_url' => 'https://www.google.com/maps/search/?api=1&query=FlavourFlow%20Spice%20House%2C%20Navrangpura%2C%20Ahmedabad%2C%20Gujarat',
    ],

    'footer' => [
        'brand' => [
            'tagline' => 'Bringing the Authentic Taste of Indian Spices to Every Kitchen.',
            'description' => 'We deliver fresh, handpicked spices sourced directly from trusted farms across India, ensuring purity, freshness, and authentic flavor.',
            'cta' => [
                'label' => 'Shop fresh spices',
                'href' => '/#products',
            ],
        ],
        'quick_links' => [
            ['label' => 'Home', 'href' => '/#top'],
            ['label' => 'Offers', 'href' => '/#offers'],
            ['label' => 'About Us', 'href' => '/#company'],
            ['label' => 'Contact Us', 'href' => '/#contact'],
        ],
        'customer_service' => [
            [
                'label' => 'Return & Refund Policy',
                'href' => 'mailto:support@flavourflow.com?subject=Return%20%26%20Refund%20Policy%20Inquiry',
            ],
            [
                'label' => 'Privacy Policy',
                'href' => 'mailto:support@flavourflow.com?subject=Privacy%20Policy%20Inquiry',
            ],
            [
                'label' => 'Terms & Conditions',
                'href' => 'mailto:support@flavourflow.com?subject=Terms%20%26%20Conditions%20Inquiry',
            ],
            [
                'label' => 'Help Center',
                'href' => 'mailto:support@flavourflow.com?subject=Help%20Center%20Support',
            ],
        ],
        'contact' => [
            'address' => 'Patan, Gujarat, India',
            'phone' => '+91 99999 99999',
            'whatsapp' => '+91 99999 99999',
            'email' => 'support@flavourflow.com',
            'hours' => [
                'Monday - Saturday',
                '9:00 AM - 7:00 PM',
            ],
        ],
        'socials' => [
            ['label' => 'WhatsApp', 'href' => 'https://wa.me/919999999999?text=Hello%20FlavourFlow%2C%20I%20have%20an%20inquiry%20regarding%20spices.', 'icon' => 'whatsapp', 'brand' => '#25D366'],
            ['label' => 'Facebook', 'href' => 'https://www.facebook.com/', 'icon' => 'facebook', 'brand' => '#1877F2'],
            ['label' => 'Instagram', 'href' => 'https://www.instagram.com/', 'icon' => 'instagram', 'brand' => '#E4405F'],
            ['label' => 'Twitter (X)', 'href' => 'https://x.com/', 'icon' => 'x', 'brand' => '#111111'],
            ['label' => 'YouTube', 'href' => 'https://www.youtube.com/', 'icon' => 'youtube', 'brand' => '#FF0033'],
            ['label' => 'Pinterest', 'href' => 'https://www.pinterest.com/', 'icon' => 'pinterest', 'brand' => '#E60023'],
            ['label' => 'LinkedIn', 'href' => 'https://www.linkedin.com/', 'icon' => 'linkedin', 'brand' => '#0A66C2'],
        ],
        'trust_badges' => [
            [
                'label' => '100% Natural',
                'icon' => 'leaf',
                'title' => '100% Natural ingredients',
                'description' => 'Made without unnecessary additives, so the spice flavour stays honest and clean.',
            ],
            [
                'label' => 'Farm Fresh',
                'icon' => 'sprout',
                'title' => 'Farm-fresh sourcing',
                'description' => 'Selected from trusted growers and packed in small batches for a fresher kitchen shelf.',
            ],
            [
                'label' => 'Secure Payments',
                'icon' => 'shield-check',
                'title' => 'Secure payment support',
                'description' => 'Built to support safer online checkout flows and protected transaction handling.',
            ],
            [
                'label' => 'Fast Delivery',
                'icon' => 'truck',
                'title' => 'Fast delivery promise',
                'description' => 'Prepared for quick dispatch so orders move from our store to your kitchen faster.',
            ],
            [
                'label' => 'Quality Tested',
                'icon' => 'quality-check',
                'title' => 'Quality tested batches',
                'description' => 'Every batch is checked for aroma, consistency, and the premium finish your cooking deserves.',
            ],
        ],
        'payments' => [
            ['label' => 'Visa', 'icon' => 'visa'],
            ['label' => 'MasterCard', 'icon' => 'mastercard'],
            ['label' => 'RuPay', 'icon' => 'rupay'],
            ['label' => 'UPI', 'icon' => 'upi'],
            ['label' => 'Google Pay', 'icon' => 'gpay'],
            ['label' => 'PhonePe', 'icon' => 'phonepe'],
            ['label' => 'Paytm', 'icon' => 'paytm'],
        ],
        'copyright' => '© 2026 FlavourFlow. All Rights Reserved.',
    ],
];
