<?php
use App\Core\Config;
$currency = Config::get('defaults')['currency_symbol'] ?? '₦';
?>
<div class="card">
  <div class="card-header">Receipt #<?= (int)($sale['id'] ?? 0) ?></div>
  <div class="card-body">
    <p>Date: <?= htmlspecialchars($sale['created_at'] ?? '') ?></p>
    <table class="table">
      <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Tax</th><th>Total</th></tr></thead>
      <tbody>
        <?php foreach (($items ?? []) as $it): ?>
          <?php $line = ($it['price'] * $it['qty']) + $it['tax']; ?>
          <tr>
            <td><?= htmlspecialchars($it['name']) ?></td>
            <td><?= (int)$it['qty'] ?></td>
            <td><?= htmlspecialchars($currency) ?><?= number_format((float)$it['price'],2) ?></td>
            <td><?= htmlspecialchars($currency) ?><?= number_format((float)$it['tax'],2) ?></td>
            <td><?= htmlspecialchars($currency) ?><?= number_format((float)$line,2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="text-end">
      <p>Subtotal: <?= htmlspecialchars($currency) ?><?= number_format((float)($sale['subtotal'] ?? 0),2) ?></p>
      <p>Tax: <?= htmlspecialchars($currency) ?><?= number_format((float)($sale['tax_total'] ?? 0),2) ?></p>
      <h5>Total: <?= htmlspecialchars($currency) ?><?= number_format((float)($sale['total_amount'] ?? 0),2) ?></h5>
    </div>
    <h6>Payments</h6>
    <ul>
      <?php foreach (($payments ?? []) as $p): ?>
        <li><?= htmlspecialchars(ucwords(str_replace('_',' ',$p['method']))) ?>: <?= htmlspecialchars($currency) ?><?= number_format((float)$p['amount'],2) ?></li>
      <?php endforeach; ?>
    </ul>
    <button class="btn btn-outline-secondary" onclick="window.print()">Print / Save as PDF</button>
    <a class="btn btn-primary" href="/pos">New Sale</a>
  </div>
</div>