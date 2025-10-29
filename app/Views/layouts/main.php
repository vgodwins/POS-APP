<?php
use App\Core\Config;
use App\Core\Auth;
use App\Core\DB;
$currencySymbol = Config::get('defaults')['currency_symbol'] ?? '₦';
$appName = Config::get('app')['name'] ?? 'Mall POS';
$theme = Config::get('defaults')['theme'] ?? 'light';
$logoUrl = '';
$currentUser = Auth::user();
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
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background-color: #f8fafc; }
    .sidebar { width: 240px; min-height: 100vh; position: fixed; top: 0; left: 0; background-color: #212529; color: #fff; padding: 16px; }
    .content { margin-left: 240px; padding: 24px; }
    .currency { font-weight: 600; }
    .brand-logo { height: 28px; width: auto; margin-right: 8px; border-radius: 4px; }
    .nav-link { color: #fff; }
    .nav-link:hover { color: #f8f9fa; }
    .app-name { font-weight: 600; }
    .card { border-radius: 8px; }
    .btn { border-radius: 6px; }
    .sidebar-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.35); display: none; z-index: 1040; }
    /* Center common headings */
    .card-header, h1, h2, h3, h4, h5 { text-align: center; }
    /* Hide sidebar and remove margin on unauthenticated pages */
    body.no-sidebar .sidebar { display: none; }
    body.no-sidebar .content { margin-left: 0; }
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .sidebar { position: fixed; width: 240px; min-height: 100vh; transform: translateX(-100%); transition: transform .2s ease; z-index: 1050; }
      body.menu-open .sidebar { transform: translateX(0); }
      body.menu-open { overflow: hidden; }
      body.menu-open .sidebar-backdrop { display: block; }
      .content { margin-left: 0; }
    }
  </style>
</head>
<body class="<?= Auth::check() ? '' : 'no-sidebar' ?>">
<div class="d-flex">
  <?php if (Auth::check()): ?>
  <aside class="sidebar" id="sidebar" aria-label="Main navigation">
    <a class="d-flex align-items-center mb-3 text-decoration-none text-white" href="/dashboard">
      <?php if ($logoUrl): ?>
        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" class="brand-logo">
      <?php endif; ?>
      <span class="app-name"><?= htmlspecialchars($appName) ?></span>
    </a>
    <button class="btn btn-sm btn-outline-light d-md-none mb-3" id="mobileMenuClose" aria-label="Close menu">Close</button>
    <ul class="nav flex-column mb-3">
      <li class="nav-item"><a class="nav-link" href="/dashboard">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" href="/pos">POS</a></li>
      <li class="nav-item"><a class="nav-link" href="/products">Products</a></li>
      <?php if (!\App\Core\Auth::hasRole('cashier')): ?>
      <li class="nav-item"><a class="nav-link" href="/categories">Categories</a></li>
      <li class="nav-item"><a class="nav-link" href="/customers">Customers</a></li>
      <li class="nav-item"><a class="nav-link" href="/customers/create">Add Customer</a></li>
      <?php endif; ?>
      <?php if (!\App\Core\Auth::hasRole('cashier')): ?>
      <li class="nav-item"><a class="nav-link" href="/vouchers">Vouchers</a></li>
      <?php endif; ?>
      <!-- Renamed Stores to Dashboard (link above). Admin can still manage stores from elsewhere -->
      <?php if (\App\Core\Auth::hasRole('admin')): ?>
      <li class="nav-item"><a class="nav-link" href="/users">Users</a></li>
      <li class="nav-item"><a class="nav-link" href="/register">Register</a></li>
      <?php endif; ?>
      <?php if (!\App\Core\Auth::hasRole('cashier')): ?>
      <li class="nav-item"><a class="nav-link" href="/reports/sales">Reports</a></li>
      <?php endif; ?>
      <?php if (\App\Core\Auth::hasRole('owner') || \App\Core\Auth::hasRole('admin')): ?>
      <li class="nav-item"><a class="nav-link" href="/subscriptions">Subscriptions</a></li>
      <?php endif; ?>
      <li class="nav-item"><a class="nav-link" href="/vouchers/scan">Scan Voucher</a></li>
      <li class="nav-item"><a class="nav-link" href="/expenses">Expenses</a></li>
      <?php if (!\App\Core\Auth::hasRole('cashier')): ?>
      <li class="nav-item"><a class="nav-link" href="/settings">Settings</a></li>
      <?php endif; ?>
      <?php if (\App\Core\Auth::hasRole('admin')): ?>
      <li class="nav-item"><a class="nav-link" href="/reports/general">General Reports</a></li>
      <?php endif; ?>
    </ul>
    <div class="mt-4 text-white-50 small">
      <div class="mb-2">Currency: <span class="currency"><?= htmlspecialchars($currencySymbol) ?></span></div>
      <?php if ($currentUser): ?>
        <div class="mb-2">Signed in as <?= htmlspecialchars($currentUser['name'] ?? '') ?></div>
      <?php endif; ?>
      <a class="btn btn-sm btn-outline-light" href="/logout">Logout</a>
    </div>
  </aside>
  <div id="sidebarBackdrop" class="sidebar-backdrop d-md-none" aria-hidden="true"></div>
  <?php endif; ?>
  <main class="content flex-grow-1">
    <div class="container">
      <?php if (Auth::check()): ?>
      <div class="d-md-none mb-3">
        <button class="btn btn-outline-secondary" id="mobileMenuToggle" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle menu">Menu</button>
      </div>
      <?php endif; ?>
      <?php foreach (flash_messages() as $fm): ?>
        <?php $cls = ($fm['type'] ?? 'info'); $cls = $cls === 'error' ? 'danger' : ($cls === 'success' ? 'success' : ($cls === 'warning' ? 'warning' : 'info')); ?>
        <div class="alert alert-<?= htmlspecialchars($cls) ?>" role="alert">
          <?= htmlspecialchars($fm['message'] ?? '') ?>
        </div>
      <?php endforeach; ?>
      <?php include $viewFile; ?>
    </div>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var body = document.body;
    var toggleBtn = document.getElementById('mobileMenuToggle');
    var closeBtn = document.getElementById('mobileMenuClose');
    var backdrop = document.getElementById('sidebarBackdrop');
    function openMenu() {
      body.classList.add('menu-open');
      if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
    }
    function closeMenu() {
      body.classList.remove('menu-open');
      if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
    }
    if (toggleBtn) {
      toggleBtn.addEventListener('click', function() {
        if (body.classList.contains('menu-open')) { closeMenu(); } else { openMenu(); }
      });
    }
    if (closeBtn) { closeBtn.addEventListener('click', closeMenu); }
    if (backdrop) { backdrop.addEventListener('click', closeMenu); }
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') { closeMenu(); } });
  });
</script>
</body>
</html>
