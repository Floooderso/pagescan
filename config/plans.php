<?php

return [
    'starter' => [
        'name'            => 'Starter',
        'price'           => 29,
        'currency'        => 'EUR',
        'scans'           => 200,
        'users'           => 1,
        'stripe_price_id' => env('STRIPE_PRICE_STARTER', ''),
    ],
    'agency' => [
        'name'            => 'Agency',
        'price'           => 79,
        'currency'        => 'EUR',
        'scans'           => 1000,
        'users'           => 3,
        'stripe_price_id' => env('STRIPE_PRICE_AGENCY', ''),
    ],
    'pro' => [
        'name'            => 'Pro',
        'price'           => 149,
        'currency'        => 'EUR',
        'scans'           => 0,
        'users'           => 10,
        'stripe_price_id' => env('STRIPE_PRICE_PRO', ''),
    ],
];
