<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain');

// Start session BEFORE any output
session_start();
$savePath = ini_get('session.save_path');
$_SESSION['health_check'] = 'ok';
session_write_close();

// Capture output and also persist to storage/logs/health-check.txt for IDE inspection
$out = '';
$write = function($line) use (&$out) { $out .= $line; echo $line; };

$write("== Test DB and Session (public) ==\n");
$write("Loaded php.ini: " . (php_ini_loaded_file() ?: '(none)') . "\n");
$write("extension_dir: " . (ini_get('extension_dir') ?: '(none)') . "\n");

$cfgFile = __DIR__ . '/../config/env.php';
if (!file_exists($cfgFile)) {
    $write("ERROR: missing config/env.php\n");
    exit;
}
$cfg = require $cfgFile;

// Test PDO MySQL connection
try {
    $db = $cfg['db'] ?? [];
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $db['host'], $db['database']);
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $write("DB: OK (connected)\n");
    // Quick sanity queries
    $pdo->query('SELECT 1');
    $hasStores = $pdo->query('SHOW TABLES LIKE "stores"')->fetch();
    $write("Table stores: " . ($hasStores ? 'FOUND' : 'MISSING') . "\n");
    $hasUsers = $pdo->query('SHOW TABLES LIKE "users"')->fetch();
    $write("Table users: " . ($hasUsers ? 'FOUND' : 'MISSING') . "\n");
    $hasSales = $pdo->query('SHOW TABLES LIKE "sales"')->fetch();
    $write("Table sales: " . ($hasSales ? 'FOUND' : 'MISSING') . "\n");
} catch (Throwable $e) {
    $write("DB ERROR: " . $e->getMessage() . "\n");
}

// Test pdo_mysql extension
if (extension_loaded('pdo_mysql')) {
    $write("Extension pdo_mysql: LOADED\n");
} else {
    $write("Extension pdo_mysql: NOT LOADED\n");
}

$write("== Done ==\n");

// Persist health-check output for offline inspection
$logDir = __DIR__ . '/../storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0777, true);
}
@file_put_contents($logDir . '/health-check.txt', $out);
