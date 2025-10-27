<?php
use App\Core\Config;
$currency = Config::get('defaults')['currency_symbol'] ?? '₦';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Sales Reports</h3>
  <div>
    <a class="btn btn-outline-secondary" href="/reports/sales/export.csv">Export CSV</a>
    <a class="btn btn-success" href="/reports/sales/filter">Filter by Date / Item</a>
    <button class="btn btn-primary" onclick="window.print()">Export PDF</button>
  </div>
</div>
<div class="row">
  <div class="col-md-3">
    <div class="card mb-3">
      <div class="card-body">
        <h6 class="card-title">Today</h6>
        <p class="mb-1">Sales: <?= (int)($today['sales_count'] ?? 0) ?></p>
        <p class="mb-1">Subtotal: <?= htmlspecialchars($currency) ?><?= number_format((float)($today['subtotal'] ?? 0),2) ?></p>
        <p class="mb-1">Tax: <?= htmlspecialchars($currency) ?><?= number_format((float)($today['tax_total'] ?? 0),2) ?></p>
        <h5>Revenue: <?= htmlspecialchars($currency) ?><?= number_format((float)($today['total_amount'] ?? 0),2) ?></h5>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card mb-3">
      <div class="card-body">
        <h6 class="card-title">This Week</h6>
        <p class="mb-1">Sales: <?= (int)($week['sales_count'] ?? 0) ?></p>
        <p class="mb-1">Subtotal: <?= htmlspecialchars($currency) ?><?= number_format((float)($week['subtotal'] ?? 0),2) ?></p>
        <p class="mb-1">Tax: <?= htmlspecialchars($currency) ?><?= number_format((float)($week['tax_total'] ?? 0),2) ?></p>
        <h5>Revenue: <?= htmlspecialchars($currency) ?><?= number_format((float)($week['total_amount'] ?? 0),2) ?></h5>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card mb-3">
      <div class="card-body">
        <h6 class="card-title">This Month</h6>
        <p class="mb-1">Sales: <?= (int)($month['sales_count'] ?? 0) ?></p>
        <p class="mb-1">Subtotal: <?= htmlspecialchars($currency) ?><?= number_format((float)($month['subtotal'] ?? 0),2) ?></p>
        <p class="mb-1">Tax: <?= htmlspecialchars($currency) ?><?= number_format((float)($month['tax_total'] ?? 0),2) ?></p>
        <h5>Revenue: <?= htmlspecialchars($currency) ?><?= number_format((float)($month['total_amount'] ?? 0),2) ?></h5>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card mb-3">
      <div class="card-body">
        <h6 class="card-title">This Year</h6>
        <p class="mb-1">Sales: <?= (int)($year['sales_count'] ?? 0) ?></p>
        <p class="mb-1">Subtotal: <?= htmlspecialchars($currency) ?><?= number_format((float)($year['subtotal'] ?? 0),2) ?></p>
        <p class="mb-1">Tax: <?= htmlspecialchars($currency) ?><?= number_format((float)($year['tax_total'] ?? 0),2) ?></p>
        <h5>Revenue: <?= htmlspecialchars($currency) ?><?= number_format((float)($year['total_amount'] ?? 0),2) ?></h5>
      </div>
    </div>
  </div>
</div>
