<?php
// One-off script to insert 'manager' and 'accountant' roles if missing

// Load env config (same pattern as migrate.php)
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

try {
    $pdo = DB::conn();
    $roles = ['manager', 'accountant'];
    $missing = [];
    foreach ($roles as $r) {
        $st = $pdo->prepare('SELECT COUNT(*) FROM roles WHERE name = ?');
        $st->execute([$r]);
        if (((int)$st->fetchColumn()) === 0) { $missing[] = $r; }
    }
    if (empty($missing)) {
        echo "Roles already present: manager, accountant\n";
        exit(0);
    }
    $placeholders = implode(',', array_fill(0, count($missing), '(?)'));
    $st = $pdo->prepare('INSERT INTO roles(name) VALUES ' . $placeholders);
    $st->execute($missing);
    echo 'Inserted roles: ' . implode(', ', $missing) . "\n";
    exit(0);
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    exit(1);
}

