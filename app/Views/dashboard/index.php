<?php
use App\Core\Config;
use App\Core\Auth;
use App\Core\DB;
$currency = Config::get('defaults')['currency_symbol'] ?? '₦';
$user = Auth::user();
$storeName = '';
if ($user && ($user['store_id'] ?? null)) {
  try {
    $pdo = DB::conn();
    $st = $pdo->prepare('SELECT name FROM stores WHERE id = ?');
    $st->execute([$user['store_id']]);
    $storeName = (string)$st->fetchColumn();
  } catch (\Throwable $e) {
    // Keep empty store name on DB error
  }
}
?>
<div class="mb-3">
  <h4>Welcome, <?= htmlspecialchars($user['name'] ?? 'User') ?><?= $storeName ? ' — ' . htmlspecialchars($storeName) : '' ?></h4>
</div>
<div class="row">
  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Today Sales</h5>
        <p class="card-text display-6"><?= (int)($metrics['sales_count'] ?? 0) ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Today Revenue</h5>
        <p class="card-text display-6"><?= htmlspecialchars($currency) ?><?= number_format((float)($metrics['total_amount'] ?? 0), 2) ?></p>
      </div>
    </div>
  </div>
</div>