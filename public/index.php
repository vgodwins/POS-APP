<?php
// Simple front controller for Mall POS App

declare(strict_types=1);

// Temporarily enable detailed errors to surface the root cause during debugging
ini_set('display_errors', '1');
error_reporting(E_ALL);

session_start();

// Load environment config
$configFile = __DIR__ . '/../config/env.php';
if (!file_exists($configFile)) {
    $example = __DIR__ . '/../config/env.example.php';
    if (!file_exists($example)) {
        http_response_code(500);
        echo 'Missing config/env.php and config/env.example.php';
        exit;
    }
    // Use example for first run
    $config = require $example;
} else {
    $config = require $configFile;
}

// Bootstrap app (autoloaders, database, helpers)
require __DIR__ . '/../app/bootstrap.php';

use App\Core\Router;
use App\Core\Request;

$router = new Router();
require __DIR__ . '/../routes/web.php';

$request = Request::capture();
try {
    $router->dispatch($request);
} catch (\Throwable $e) {
    http_response_code(500);
    echo 'Application error: ' . htmlspecialchars($e->getMessage());
}
