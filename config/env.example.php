<?php
return [
    'app' => [
        'name' => 'Mall POS',
        'url' => 'http://localhost:8000',
        'timezone' => 'Africa/Lagos',
        'env' => 'development',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'database' => 'pos_app',
        'user' => 'root',
        'pass' => '',
    ],
    'mail' => [
        'host' => 'smtp.example.com',
        'port' => 587,
        'user' => 'username',
        'pass' => 'password',
        'from' => 'noreply@example.com',
    ],
    'defaults' => [
        'currency_code' => 'NGN',
        'currency_symbol' => '₦',
        'tax_rate' => 0.075,
        'theme' => 'light',
        'low_stock_threshold' => 5,
    ],
    'paystack' => [
        'public_key' => 'pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxx',
        'secret_key' => 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxx',
        // If not set, will default to app.url + '/subscriptions/paystack/callback'
        'callback_url' => 'http://localhost:8000/subscriptions/paystack/callback',
    ],
];
