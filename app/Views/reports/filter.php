<?php
use App\Core\Config;
$currency = Config::get('defaults')['currency_symbol'] ?? '₦';
$filters = $filters ?? [];
$from = $filters['from'] ?? '';
$to = $filters['to'] ?? '';
$pid = $filters['product_id'] ?? '';
$cid = $filters['category_id'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Filter Sales Report</h3>
  <a class="btn btn-outline-secondary" href="/reports/sales">Back</a>
  </div>
<div class="card mb-3">
  <div class="card-body">
    <form method="get" action="/reports/sales/filter" class="row g-3">
      <div class="col-md-3">
        <label class="form-label">From</label>
        <input type="date" class="form-control" name="from" value="<?= htmlspecialchars($from) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">To</label>
        <input type="date" class="form-control" name="to" value="<?= htmlspecialchars($to) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Category</label>
        <select class="form-select" name="category_id">
          <option value="">All Categories</option>
          <?php foreach (($categories ?? []) as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= ((int)$cid) === ((int)$c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Item</label>
        <select class="form-select" name="product_id">
          <option value="">All Items</option>
          <?php foreach (($products ?? []) as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= ((int)$pid) === ((int)$p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary" type="submit">Apply</button>
      </div>
    </form>
  </div>
</div>

<div class="row">
  <div class="col-md-4">
    <div class="card mb-3">
      <div class="card-body">
        <h6 class="card-title">Revenue</h6>
        <h4><?= htmlspecialchars($currency) ?><?= number_format((float)($summary['revenue'] ?? 0),2) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card mb-3">
      <div class="card-body">
        <h6 class="card-title">Subtotal</h6>
        <h4><?= htmlspecialchars($currency) ?><?= number_format((float)($summary['subtotal'] ?? 0),2) ?></h4>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card mb-3">
      <div class="card-body">
        <h6 class="card-title">Profit (approx)</h6>
        <h4><?= htmlspecialchars($currency) ?><?= number_format((float)($profit ?? 0),2) ?></h4>
      </div>
    </div>
  </div>
</div>
