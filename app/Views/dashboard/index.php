<?php
use App\Core\Config;
$currency = Config::get('defaults')['currency_symbol'] ?? '₦';
?>
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