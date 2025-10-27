<?php
use App\Core\Config;
$currencySymbol = $store['currency_symbol'] ?? (Config::get('defaults')['currency_symbol'] ?? '₦');
$currencyCode = $store['currency_code'] ?? (Config::get('defaults')['currency_code'] ?? 'NGN');
$storeName = (string)($store['name'] ?? 'Business');
$companyNumber = (string)($store['company_number'] ?? '');
$storePhone = (string)($store['phone'] ?? '');
$storeAddress = (string)($store['address'] ?? '');
$logoUrl = (string)($store['logo_url'] ?? '');
$amountPaid = 0.0;
foreach (($payments ?? []) as $p) { $amountPaid += (float)($p['amount'] ?? 0); }
$totalAmount = (float)($sale['total_amount'] ?? 0);
$balanceDue = max($totalAmount - $amountPaid, 0);
?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <strong>Invoice</strong>
    <div>
      <button class="btn btn-outline-secondary" onclick="window.print()">Print / PDF</button>
      <a class="btn btn-primary" href="/pos">New Sale</a>
    </div>
  </div>
  <div class="card-body">
    <div class="row mb-3 align-items-center">
      <div class="col-md-6">
        <div class="d-flex align-items-center gap-3">
          <?php if ($logoUrl): ?>
            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" style="height:60px; width:auto; object-fit:contain;">
          <?php endif; ?>
          <div>
            <h5 class="mb-1"><?= htmlspecialchars($storeName) ?></h5>
            <?php if ($companyNumber): ?><div>RC: <?= htmlspecialchars($companyNumber) ?></div><?php endif; ?>
            <?php if ($storePhone): ?><div>Phone: <?= htmlspecialchars($storePhone) ?></div><?php endif; ?>
            <?php if ($storeAddress): ?><div>Address: <?= htmlspecialchars($storeAddress) ?></div><?php endif; ?>
          </div>
        </div>
      </div>
      <div class="col-md-6 text-end">
        <div><strong>Invoice #</strong> <?= (int)($sale['id'] ?? 0) ?></div>
        <div><strong>Date</strong> <?= htmlspecialchars($sale['created_at'] ?? '') ?></div>
        <div><strong>Currency</strong> <?= htmlspecialchars($currencyCode) ?> (<?= htmlspecialchars($currencySymbol) ?>)</div>
      </div>
    </div>

    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Item</th>
          <th class="text-end">Qty</th>
          <th class="text-end">Price</th>
          <th class="text-end">Tax</th>
          <th class="text-end">Line Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($items ?? []) as $it): ?>
          <?php $lineTotal = ($it['price'] * $it['qty']) + $it['tax']; ?>
          <tr>
            <td><?= htmlspecialchars($it['name']) ?></td>
            <td class="text-end"><?= (int)$it['qty'] ?></td>
            <td class="text-end"><?= htmlspecialchars($currencySymbol) ?><?= number_format((float)$it['price'], 2) ?></td>
            <td class="text-end"><?= htmlspecialchars($currencySymbol) ?><?= number_format((float)$it['tax'], 2) ?></td>
            <td class="text-end"><?= htmlspecialchars($currencySymbol) ?><?= number_format((float)$lineTotal, 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="4" class="text-end">Subtotal</th>
          <th class="text-end"><?= htmlspecialchars($currencySymbol) ?><?= number_format((float)($sale['subtotal'] ?? 0), 2) ?></th>
        </tr>
        <tr>
          <th colspan="4" class="text-end">Tax</th>
          <th class="text-end"><?= htmlspecialchars($currencySymbol) ?><?= number_format((float)($sale['tax_total'] ?? 0), 2) ?></th>
        </tr>
        <tr>
          <th colspan="4" class="text-end">Total</th>
          <th class="text-end"><?= htmlspecialchars($currencySymbol) ?><?= number_format((float)($sale['total_amount'] ?? 0), 2) ?></th>
        </tr>
      </tfoot>
    </table>

    <div class="row">
      <div class="col-md-6">
        <div class="mb-3">
          <h6>Payments</h6>
          <ul class="list-group">
            <?php foreach (($payments ?? []) as $p): ?>
              <li class="list-group-item d-flex justify-content-between">
                <span><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string)$p['method']))) ?></span>
                <span><?= htmlspecialchars($currencySymbol) ?><?= number_format((float)$p['amount'], 2) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <h6>Summary</h6>
          <table class="table table-sm">
            <tr>
              <td class="text-end"><strong>Total</strong></td>
              <td class="text-end"><?= htmlspecialchars($currencySymbol) ?><?= number_format((float)$totalAmount, 2) ?></td>
            </tr>
            <tr>
              <td class="text-end">Amount Paid</td>
              <td class="text-end"><?= htmlspecialchars($currencySymbol) ?><?= number_format((float)$amountPaid, 2) ?></td>
            </tr>
            <tr>
              <td class="text-end">Balance Due</td>
              <td class="text-end"><?= htmlspecialchars($currencySymbol) ?><?= number_format((float)$balanceDue, 2) ?></td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <div class="mt-3">
      <h6>Terms & Conditions</h6>
      <ul class="small text-muted">
        <li>All sales are subject to applicable taxes.</li>
        <li>Goods sold in good condition are not returnable.</li>
        <li>Keep this invoice for warranty and support queries.</li>
      </ul>
    </div>

    <?php $app = Config::get('app') ?? []; $appName = $app['name'] ?? 'Mall POS'; $appUrl = $app['url'] ?? ''; ?>
    <div class="mt-4 text-muted d-flex justify-content-between align-items-center">
      <span>Thank you for your business.</span>
      <span><?= htmlspecialchars($appName) ?><?= $appUrl ? ' — ' . htmlspecialchars($appUrl) : '' ?></span>
    </div>
  </div>
</div>
