<?php

return [
    'currency' => '₹',
    'free_shipping_threshold' => 999,
    'flat_shipping' => 79,
    'per_page' => 12,
    'max_qty_per_line' => 20,

    // Shown in the footer / contact page only when filled in (.env: SHOP_EMAIL, SHOP_PHONE, SHOP_ADDRESS, SOCIAL_*).
    'contact' => [
        'email' => env('SHOP_EMAIL'),
        'phone' => env('SHOP_PHONE'),
        'address' => env('SHOP_ADDRESS'),
    ],
    'social' => array_filter([
        'Instagram' => env('SOCIAL_INSTAGRAM'),
        'Facebook' => env('SOCIAL_FACEBOOK'),
        'X' => env('SOCIAL_X'),
        'YouTube' => env('SOCIAL_YOUTUBE'),
    ]),
];
