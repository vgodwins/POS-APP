<?php
// Simple front controller for Mall POS App

declare(strict_types=1);

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
$router->dispatch($request);