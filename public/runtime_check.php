<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain');

$out = '';
$write = function($line) use (&$out) { $out .= $line; echo $line; };

$write("== Runtime Check ==\n");
$write("SAPI: " . php_sapi_name() . "\n");
$write("PHP Version: " . PHP_VERSION . "\n");
$write("PHP_BINARY: " . PHP_BINARY . "\n");
$write("PHPRC: " . (getenv('PHPRC') ?: '(none)') . "\n");
$port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 'unknown';
$write("Server port: " . $port . "\n");
$write("Loaded php.ini: " . (php_ini_loaded_file() ?: '(none)') . "\n");
$write("extension_dir: " . (ini_get('extension_dir') ?: '(none)') . "\n");
$scan = getenv('PHP_INI_SCAN_DIR');
$write("PHP_INI_SCAN_DIR: " . ($scan ?: '(none)') . "\n");

// Extensions
$exts = get_loaded_extensions();
sort($exts);
$write("Loaded extensions (" . count($exts) . "): " . implode(', ', $exts) . "\n");

// PDO
if (class_exists('PDO')) {
    $write("PDO: AVAILABLE\n");
    try {
        $drivers = PDO::getAvailableDrivers();
        $write("PDO drivers (" . count($drivers) . "): " . implode(', ', $drivers) . "\n");
    } catch (Throwable $e) {
        $write("PDO drivers: ERROR - " . $e->getMessage() . "\n");
    }
} else {
    $write("PDO: NOT AVAILABLE\n");
}

$write("extension_loaded('pdo_mysql'): " . (extension_loaded('pdo_mysql') ? 'true' : 'false') . "\n");

$write("== Done ==\n");

// Persist to logs for IDE inspection
try {
    $logDir = __DIR__ . '/../storage/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    @file_put_contents($logDir . '/runtime-check-' . $port . '.txt', $out);
} catch (Throwable $e) {
    // ignore
}
