<?php
use App\Core\Config;
use App\Core\Auth;
use App\Core\DB;
$currency = Config::get('defaults')['currency_symbol'] ?? '₦';
$user = Auth::user();
$storeName = '';
try {
  $sid = \App\Core\Auth::effectiveStoreId();
  if ($sid) {
    $pdo = DB::conn();
    $st = $pdo->prepare('SELECT name FROM stores WHERE id = ?');
    $st->execute([$sid]);
    $storeName = (string)$st->fetchColumn();
  }
} catch (\Throwable $e) {
  // Keep empty store name on DB error
}
?>
<div class="mb-3 text-center">
  <h4>Welcome, <?= htmlspecialchars($user['name'] ?? 'User') ?><?= $storeName ? ' — ' . htmlspecialchars($storeName) : '' ?></h4>
  <div class="text-muted">Dashboard</div>
  <hr>
</div>
<?php if (\App\Core\Auth::hasRole('admin')): ?>
<div class="mb-3">
  <form method="post" action="/admin/store/switch" class="d-flex align-items-center gap-2">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
    <label class="form-label mb-0">View Store</label>
    <select name="store_id" class="form-select" style="max-width: 320px;">
      <option value="">My Store (Default)</option>
      <?php foreach (($stores ?? []) as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= (!empty($selectedStoreId) && (int)$selectedStoreId === (int)$s['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($s['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-outline-primary" type="submit">Switch</button>
  </form>
  <small class="text-muted">Switch context to analyze a different shop.</small>
  <hr>
  </div>
<?php endif; ?>
<div class="row">
  <div class="col-md-7">
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Today Sales</h5>
        <p class="card-text display-6"><?= (int)($metrics['sales_count'] ?? 0) ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-5">
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Today Revenue</h5>
        <p class="card-text display-6"><?= htmlspecialchars($currency) ?><?= number_format((float)($metrics['total_amount'] ?? 0), 2) ?></p>
      </div>
    </div>
  </div>
</div>
<?php if (\App\Core\Auth::hasRole('admin')): ?>
<div class="row">
  <div class="col-md-7"></div>
  <div class="col-md-5">
    <div class="card mb-3">
      <div class="card-header">User Activity</div>
      <div class="card-body">
        <?php if (!empty($recentUsers)): ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($recentUsers as $ru): ?>
              <li class="list-group-item d-flex justify-content-between">
                <span><?= htmlspecialchars($ru['name'] ?? '') ?> (<?= htmlspecialchars($ru['email'] ?? '') ?>)</span>
                <span class="text-muted small"><?= htmlspecialchars($ru['last_login_at'] ?? '—') ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="text-muted">No recent user activity</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
