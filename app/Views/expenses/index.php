<?php
use App\Core\Config;
$currency = Config::get('defaults')['currency_symbol'] ?? '₦';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Expenses</h4>
  <a class="btn btn-primary" href="/expenses/create">Add Expense</a>
</div>
<div class="row mb-4">
  <div class="col-md-3"><div class="card"><div class="card-body"><h6>Today</h6><h5><?= htmlspecialchars($currency) ?><?= number_format((float)($summary['today'] ?? 0),2) ?></h5></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><h6>This Week</h6><h5><?= htmlspecialchars($currency) ?><?= number_format((float)($summary['week'] ?? 0),2) ?></h5></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><h6>This Month</h6><h5><?= htmlspecialchars($currency) ?><?= number_format((float)($summary['month'] ?? 0),2) ?></h5></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><h6>This Year</h6><h5><?= htmlspecialchars($currency) ?><?= number_format((float)($summary['year'] ?? 0),2) ?></h5></div></div></div>
</div>
<table class="table table-striped">
  <thead><tr><th>Date</th><th>Category</th><th>Amount</th><th>Note</th></tr></thead>
  <tbody>
  <?php foreach (($expenses ?? []) as $ex): ?>
    <tr>
      <td><?= htmlspecialchars($ex['created_at'] ?? '') ?></td>
      <td><?= htmlspecialchars($ex['category'] ?? '') ?></td>
      <td><?= htmlspecialchars($currency) ?><?= number_format((float)($ex['amount'] ?? 0),2) ?></td>
      <td><?= htmlspecialchars($ex['note'] ?? '') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>