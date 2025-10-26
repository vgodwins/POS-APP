<?php
// Local development config
return [
    'app' => [
        'name' => 'Mall POS',
        'url' => 'http://127.0.0.1:8000',
        'timezone' => 'Africa/Lagos',
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
    ],
];