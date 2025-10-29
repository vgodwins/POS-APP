<?php
$plans = $plans ?? [];
$level = $level ?? 'store';
?>
<div class="row justify-content-center">
  <div class="col-md-10">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="mb-0">Subscriptions — <?= htmlspecialchars(strtoupper($level)) ?> Level</h4>
        <small class="text-muted">Choose a plan and pay securely via Paystack.</small>
      </div>
      <?php if (\App\Core\Auth::hasRole('admin')): ?>
      <div class="btn-group">
        <a class="btn btn-outline-primary <?= $level==='store'?'active':'' ?>" href="/subscriptions?level=store">Store Level</a>
        <a class="btn btn-outline-primary <?= $level==='app'?'active':'' ?>" href="/subscriptions?level=app">App Level</a>
      </div>
      <?php endif; ?>
    </div>

    <div class="row">
      <?php foreach ($plans as $p): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card mb-3">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
              <div class="mb-2 text-muted"><?= htmlspecialchars(ucfirst($p['period'])) ?> — <?= htmlspecialchars(strtoupper($p['currency_code'])) ?></div>
              <div class="display-6 mb-3"><?= number_format((float)$p['amount'], 2) ?></div>
              <form method="post" action="/subscriptions/paystack/start" class="d-flex gap-2">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="plan_code" value="<?= htmlspecialchars($p['code']) ?>">
                <input type="hidden" name="level" value="<?= htmlspecialchars($level) ?>">
                <button class="btn btn-success" type="submit">Subscribe via Paystack</button>
                <button class="btn btn-outline-secondary" type="button" disabled title="Coming soon">Other Gateways</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($plans)): ?>
        <div class="col-12">
          <div class="alert alert-info">No plans available. Please run migrations.</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

