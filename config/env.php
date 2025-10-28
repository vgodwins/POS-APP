<?php
// Local development config
return [
    'app' => [
        'name' => 'MavicFy',
        'url' => 'http://127.0.0.1:8001',
        'timezone' => 'Africa/Lagos',
        'env' => 'development',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'database' => 'wslscvqf_pos_app',
        'user' => 'wslscvqf_victor_user',
        'pass' => 'OA_Ee$#,qE_!y7OB',
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
];
