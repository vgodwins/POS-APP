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
<?php if (\App\Core\Auth::hasRole('admin') || \App\Core\Auth::hasRole('owner') || \App\Core\Auth::hasRole('manager') || \App\Core\Auth::hasRole('accountant')): ?>
<div class="card mt-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <strong>Inventory Summary</strong>
    <div>
      <a class="btn btn-sm btn-outline-secondary" href="/products/export.csv<?= isset($inventory['category_id']) && $inventory['category_id'] ? ('?category_id=' . (int)$inventory['category_id']) : '' ?>">Export Inventory CSV</a>
    </div>
  </div>
  <div class="card-body">
    <form class="row g-2 mb-3" method="get" action="/dashboard">
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <?php $sel = strtolower($inventory['status'] ?? ''); ?>
        <select class="form-select" name="inventory_status">
          <option value="" <?= $sel === '' ? 'selected' : '' ?>>All</option>
          <option value="valid" <?= $sel === 'valid' ? 'selected' : '' ?>>Valid</option>
          <option value="expired" <?= $sel === 'expired' ? 'selected' : '' ?>>Expired</option>
          <option value="damaged" <?= $sel === 'damaged' ? 'selected' : '' ?>>Damaged</option>
          <option value="returned" <?= $sel === 'returned' ? 'selected' : '' ?>>Returned</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Category</label>
        <select class="form-select" name="inventory_category_id">
          <option value="">All</option>
          <?php foreach (($categories ?? []) as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>" <?= (isset($inventory['category_id']) && $inventory['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name'] ?? '') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button class="btn btn-primary" type="submit">Apply Filters</button>
      </div>
    </form>
    <?php if ($inventory): ?>
      <div class="row text-center">
        <div class="col-md-3">
          <div class="p-2 border rounded">
            <div class="fw-bold">Products</div>
            <div><?= (int)($inventory['items'] ?? 0) ?></div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="p-2 border rounded">
            <div class="fw-bold">Total Units</div>
            <div><?= (int)($inventory['units'] ?? 0) ?></div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="p-2 border rounded">
            <div class="fw-bold">Stock Value (Price)</div>
            <div><?= htmlspecialchars(($currency ?? (\App\Core\Config::get('defaults')['currency_symbol'] ?? '₦'))) ?><?= number_format((float)($inventory['value_price'] ?? 0), 2) ?></div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="p-2 border rounded">
            <div class="fw-bold">Stock Value (Cost)</div>
            <div><?= htmlspecialchars(($currency ?? (\App\Core\Config::get('defaults')['currency_symbol'] ?? '₦'))) ?><?= number_format((float)($inventory['value_cost'] ?? 0), 2) ?></div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <p class="text-muted mb-0">No inventory data available.</p>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
<?php if (\App\Core\Auth::hasRole('admin') || \App\Core\Auth::hasRole('owner')): ?>
<div class="mb-3 d-flex gap-2">
  <?php if (\App\Core\Auth::hasRole('admin')): ?>
  <a class="btn btn-success" href="/customers/create">Add Customer</a>
  <?php endif; ?>
  <a class="btn btn-outline-primary" href="/vouchers/scan">Scan Voucher</a>
</div>
<?php endif; ?>

<?php if (\App\Core\Auth::hasRole('admin')): ?>
<?php
$isLocked = false;
try {
  $sidSel = $selectedStoreId ?? null;
  if ($sidSel) {
    $pdo = DB::conn();
    $st = $pdo->prepare('SELECT locked FROM stores WHERE id = ?');
    $st->execute([$sidSel]);
    $isLocked = ((int)$st->fetchColumn()) === 1;
  }
} catch (\Throwable $e) { $isLocked = false; }
?>
<div class="mb-3 d-flex gap-2">
  <form method="post" action="/admin/store/<?= $isLocked ? 'resume' : 'pause' ?>">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
    <input type="hidden" name="store_id" value="<?= (int)($selectedStoreId ?? 0) ?>">
    <button class="btn btn-warning" type="submit" <?= empty($selectedStoreId) ? 'disabled' : '' ?>><?= $isLocked ? 'Resume Shop' : 'Pause Shop' ?></button>
  </form>
  <form method="post" action="/admin/store/delete" onsubmit="return confirm('Delete this shop? This cannot be undone.');">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
    <input type="hidden" name="store_id" value="<?= (int)($selectedStoreId ?? 0) ?>">
    <button class="btn btn-danger" type="submit" <?= empty($selectedStoreId) ? 'disabled' : '' ?>>Delete Shop</button>
  </form>
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
