<?php $s = $summary ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>General Reports</h3>
</div>
<div class="row g-3">
  <div class="col-md-3">
    <div class="card"><div class="card-body">
      <div class="text-muted">Stores</div>
      <div class="display-6"><?= (int)($s['stores'] ?? 0) ?></div>
    </div></div>
  </div>
  <div class="col-md-3">
    <div class="card"><div class="card-body">
      <div class="text-muted">Products</div>
      <div class="display-6"><?= (int)($s['products'] ?? 0) ?></div>
    </div></div>
  </div>
  <div class="col-md-3">
    <div class="card"><div class="card-body">
      <div class="text-muted">Vouchers</div>
      <div class="display-6"><?= (int)($s['vouchers'] ?? 0) ?></div>
    </div></div>
  </div>
  <div class="col-md-3">
    <div class="card"><div class="card-body">
      <div class="text-muted">Customers</div>
      <div class="display-6"><?= (int)($s['customers'] ?? 0) ?></div>
    </div></div>
  </div>
</div>
<div class="row g-3 mt-1">
  <div class="col-md-4">
    <div class="card"><div class="card-body">
      <div class="text-muted">Sales</div>
      <div class="display-6"><?= (int)($s['sales_count'] ?? 0) ?></div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card"><div class="card-body">
      <div class="text-muted">Revenue (All Stores)</div>
      <div class="display-6">
        <?= number_format((float)($s['revenue'] ?? 0), 2) ?>
      </div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card"><div class="card-body">
      <div class="text-muted">Expenses (This Year)</div>
      <div class="display-6">
        <?= number_format((float)($s['expenses'] ?? 0), 2) ?>
      </div>
    </div></div>
  </div>
</div>
