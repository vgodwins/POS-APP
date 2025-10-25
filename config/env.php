<?php
// Copy of env.example.php; update values for your environment
return [
    'app' => [
        'name' => 'Mall POS',
        'url' => 'https://tuckshop.mavicapp.com.ng/',
        'timezone' => 'Africa/Lagos',
    ],
    'db' => [
        'host' => 'localhost',
        'database' => 'wslscvqf_pos_app',
        'user' => 'wslscvqf_pos_user',
        'pass' => '7,yp$bUt_[rG;aI{',
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