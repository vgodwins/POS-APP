<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain');

echo "== Test DB and Session (public) ==\n";

$cfgFile = __DIR__ . '/../config/env.php';
if (!file_exists($cfgFile)) {
    echo "ERROR: missing config/env.php\n";
    exit;
}
$cfg = require $cfgFile;

// Test session
session_start();
$savePath = ini_get('session.save_path');
$_SESSION['health_check'] = 'ok';
session_write_close();
echo "Session: OK (path: {$savePath})\n";

// Test PDO MySQL connection
try {
    $db = $cfg['db'] ?? [];
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $db['host'], $db['database']);
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "DB: OK (connected)\n";
    // Quick sanity queries
    $pdo->query('SELECT 1');
    $hasStores = $pdo->query('SHOW TABLES LIKE "stores"')->fetch();
    echo "Table stores: " . ($hasStores ? 'FOUND' : 'MISSING') . "\n";
    $hasUsers = $pdo->query('SHOW TABLES LIKE "users"')->fetch();
    echo "Table users: " . ($hasUsers ? 'FOUND' : 'MISSING') . "\n";
    $hasSales = $pdo->query('SHOW TABLES LIKE "sales"')->fetch();
    echo "Table sales: " . ($hasSales ? 'FOUND' : 'MISSING') . "\n";
} catch (Throwable $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}

// Test pdo_mysql extension
if (extension_loaded('pdo_mysql')) {
    echo "Extension pdo_mysql: LOADED\n";
} else {
    echo "Extension pdo_mysql: NOT LOADED\n";
}

echo "== Done ==\n";
