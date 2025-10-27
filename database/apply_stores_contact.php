<?php
// One-off script to apply stores contact/branding columns (004_stores_contact)
// Safe to run multiple times: checks for column existence before ALTER TABLE

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
use App\Core\Config;

try {
    $pdo = DB::conn();
    $dbCfg = Config::get('db');
    $dbName = $dbCfg['database'] ?? null;
    if (!$dbName) {
        echo "Error: Database name is missing from config.\n";
        exit(1);
    }

    $columns = [
        'address'  => 'VARCHAR(255) NULL',
        'phone'    => 'VARCHAR(30) NULL',
        'logo_url' => 'VARCHAR(255) NULL',
    ];

    $missing = [];
    foreach ($columns as $name => $definition) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'stores' AND COLUMN_NAME = ?"
        );
        $stmt->execute([$dbName, $name]);
        $exists = ((int)$stmt->fetchColumn()) > 0;
        echo sprintf("%s: %s\n", $name, $exists ? 'EXISTS' : 'MISSING');
        if (!$exists) {
            $missing[$name] = $definition;
        }
    }

    if (empty($missing)) {
        echo "No changes needed. Migration already applied.\n";
        exit(0);
    }

    $parts = [];
    foreach ($missing as $n => $def) {
        $parts[] = "ADD COLUMN $n $def";
    }
    $sql = "ALTER TABLE stores " . implode(', ', $parts) . ";";

    $pdo->exec($sql);
    echo "Added columns: " . implode(', ', array_keys($missing)) . "\n";
    echo "Migration applied successfully.\n";
    exit(0);
} catch (\PDOException $e) {
    echo "PDO Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (\Throwable $t) {
    echo "Error: " . $t->getMessage() . "\n";
    exit(1);
}
