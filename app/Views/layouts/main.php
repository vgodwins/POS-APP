<?php
use App\Core\Config;
use App\Core\Auth;
use App\Core\DB;
$currencySymbol = Config::get('defaults')['currency_symbol'] ?? '₦';
$appName = Config::get('app')['name'] ?? 'Mall POS';
$theme = Config::get('defaults')['theme'] ?? 'light';
$logoUrl = '';
$user = Auth::user();
if (Auth::check()) {
  $sid = Auth::user()['store_id'] ?? null;
  if ($sid) {
    try {
      $pdo = DB::conn();
      $st = $pdo->prepare('SELECT name, currency_symbol, theme, logo_url FROM stores WHERE id = ?');
      $st->execute([$sid]);
      $row = $st->fetch(\PDO::FETCH_ASSOC);
      if ($row) {
        if (!empty($row['currency_symbol'])) { $currencySymbol = $row['currency_symbol']; }
        if (!empty($row['theme'])) { $theme = $row['theme']; }
        if (!empty($row['name'])) { $appName = $row['name']; }
        if (!empty($row['logo_url'])) { $logoUrl = $row['logo_url']; }
      }
    } catch (\Throwable $e) {
      // Keep defaults on DB error
    }
  }
}
?>
<!doctype html>
<html lang="en" data-theme="<?= htmlspecialchars($theme) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($appName) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { padding-top: 60px; }
    .currency { font-weight: 600; }
    .brand-logo { height: 28px; width: auto; margin-right: 8px; border-radius: 4px; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="/dashboard">
      <?php if ($logoUrl): ?>
        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" class="brand-logo">
      <?php endif; ?>
      <span><?= htmlspecialchars($appName) ?></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="/pos">POS</a></li>
        <li class="nav-item"><a class="nav-link" href="/products">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="/vouchers">Vouchers</a></li>
        <li class="nav-item"><a class="nav-link" href="/stores">Stores</a></li>
        <li class="nav-item"><a class="nav-link" href="/reports/sales">Reports</a></li>
        <li class="nav-item"><a class="nav-link" href="/expenses">Expenses</a></li>
        <li class="nav-item"><a class="nav-link" href="/settings">Settings</a></li>
      </ul>
      <span class="navbar-text me-3 currency">Currency: <?= htmlspecialchars($currencySymbol) ?></span>
      <?php if ($user): ?>
        <span class="navbar-text me-3">Signed in as <?= htmlspecialchars($user['name'] ?? '') ?></span>
      <?php endif; ?>
      <a class="btn btn-outline-light" href="/logout">Logout</a>
    </div>
  </div>
</nav>
<div class="container">
  <?php include $viewFile; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>