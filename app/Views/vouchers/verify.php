<?php $ok = (bool)($status['ok'] ?? false); $msg = (string)($status['message'] ?? ''); $cur = (string)($currencySymbol ?? '₦'); $storeName = (string)($store['name'] ?? ''); $logoUrl = (string)($store['logo_url'] ?? ''); $balance = !empty($voucher) ? (float)($voucher['value'] ?? 0) : 0.0; ?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Voucher Verification</div>
      <div class="card-body">
        <?php if ($storeName || $logoUrl): ?>
          <div class="d-flex align-items-center gap-3 mb-2">
            <?php if ($logoUrl): ?><img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" style="height:40px; width:auto; object-fit:contain;"><?php endif; ?>
            <?php if ($storeName): ?><strong><?= htmlspecialchars($storeName) ?></strong><?php endif; ?>
          </div>
        <?php endif; ?>
        <div class="alert <?= $ok ? 'alert-success' : 'alert-danger' ?>"><?= htmlspecialchars($msg) ?></div>
        <div class="alert <?= $ok ? 'alert-info' : 'alert-secondary' ?>">Voucher balance: <?= htmlspecialchars($cur) ?><?= number_format($balance, 2) ?></div>
        <?php if (!empty($voucher)): ?>
          <dl class="row">
            <dt class="col-sm-3">Code</dt><dd class="col-sm-9"><code><?= htmlspecialchars($voucher['code']) ?></code></dd>
            <dt class="col-sm-3">Value</dt><dd class="col-sm-9"><?= htmlspecialchars($cur) ?><?= number_format((float)$voucher['value'], 2) ?> (<?= htmlspecialchars($voucher['currency_code']) ?>)</dd>
            <dt class="col-sm-3">Expiry</dt><dd class="col-sm-9"><?= htmlspecialchars($voucher['expiry_date']) ?></dd>
            <dt class="col-sm-3">Status</dt><dd class="col-sm-9"><?= htmlspecialchars($voucher['status']) ?></dd>
          </dl>
        <?php endif; ?>
        <div class="mt-3">
          <a href="/vouchers/scan" class="btn btn-primary">Scan Another</a>
        </div>
      </div>
    </div>
  </div>
</div>
