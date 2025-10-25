<?php
use App\Core\Config;
use App\Core\Auth;
use App\Core\DB;
$currencySymbol = Config::get('defaults')['currency_symbol'] ?? '₦';
if (Auth::check()) {
  $sid = Auth::user()['store_id'] ?? null;
  if ($sid) {
    $pdo = DB::conn();
    $st = $pdo->prepare('SELECT currency_symbol FROM stores WHERE id = ?');
    $st->execute([$sid]);
    $sym = $st->fetchColumn();
    if ($sym) { $currencySymbol = $sym; }
  }
}
$appName = Config::get('app')['name'] ?? 'Mall POS';
$theme = Config::get('defaults')['theme'] ?? 'light';
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
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="/dashboard"><?= htmlspecialchars($appName) ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="/pos">POS</a></li>
        <li class="nav-item"><a class="nav-link" href="/products">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="/vouchers">Vouchers</a></li>
        <li class="nav-item"><a class="nav-link" href="/stores">Stores</a></li>
      </ul>
      <span class="navbar-text me-3 currency">Currency: <?= htmlspecialchars($currencySymbol) ?></span>
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