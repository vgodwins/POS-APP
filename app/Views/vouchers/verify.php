<?php $ok = (bool)($status['ok'] ?? false); $msg = (string)($status['message'] ?? ''); ?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Voucher Verification</div>
      <div class="card-body">
        <div class="alert <?= $ok ? 'alert-success' : 'alert-danger' ?>"><?= htmlspecialchars($msg) ?></div>
        <?php if (!empty($voucher)): ?>
          <dl class="row">
            <dt class="col-sm-3">Code</dt><dd class="col-sm-9"><code><?= htmlspecialchars($voucher['code']) ?></code></dd>
            <dt class="col-sm-3">Value</dt><dd class="col-sm-9"><?= number_format((float)$voucher['value'], 2) ?> <?= htmlspecialchars($voucher['currency_code']) ?></dd>
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
