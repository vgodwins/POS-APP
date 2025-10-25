<?php
// Simple migration runner: executes all .sql files in migrations directory
// Load environment config similar to public/index.php
$configFile = __DIR__ . '/../config/env.php';
$example = __DIR__ . '/../config/env.example.php';
if (!file_exists($configFile) && file_exists($example)) {
  $config = require $example;
} else if (file_exists($configFile)) {
  $config = require $configFile;
} else {
  $config = [];
}

require __DIR__ . '/../app/bootstrap.php';
use App\Core\DB;

$pdo = DB::conn();
$dir = __DIR__ . '/migrations';
$files = glob($dir . '/*.sql');
usort($files, function ($a, $b) { return strcmp($a, $b); });

foreach ($files as $file) {
    $sql = file_get_contents($file);
    echo "Running: " . basename($file) . "\n";
    $pdo->exec($sql);
}

echo "Migrations complete.\n";