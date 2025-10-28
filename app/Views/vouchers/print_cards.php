<?php
use App\Core\Config;
$host = Config::get('app')['url'] ?? '';
$storeName = $store['name'] ?? '';
$logoUrl = $store['logo_url'] ?? '';
$showAmount = isset($showAmount) ? (bool)$showAmount : true;
?>
<style>
  @media print {
    @page { size: A4; margin: 10mm; }
    .no-print { display: none !important; }
  }
  .print-actions { margin-bottom: 12px; }
  .cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
  .voucher-card { border: 1px solid #ddd; border-radius: 8px; padding: 12px; position: relative; }
  .voucher-header { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
  .voucher-logo { height: 28px; width: auto; border-radius: 4px; }
  .voucher-title { font-weight: 700; }
  .voucher-field { margin: 6px 0; }
  .voucher-code { font-family: monospace; font-size: 1.1rem; }
  .qr-box { position: absolute; top: 12px; right: 12px; }
  .hint { font-size: 0.85rem; color: #555; }
</style>
<div class="d-flex justify-content-between align-items-center print-actions no-print">
  <div>
    <h4 class="mb-0">Voucher Cards</h4>
    <div class="text-muted">Arranged for A4, ready to print</div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-secondary" href="/vouchers">Back</a>
    <button class="btn btn-primary" onclick="window.print()">Print A4</button>
  </div>
</div>
<div class="cards-grid">
  <?php foreach (($cards ?? []) as $entry): ?>
    <?php $v = $entry['voucher'] ?? []; $c = $entry['customer'] ?? null; $code = $v['code'] ?? ''; $verifyUrl = ($host ? rtrim($host, '/') : '') . '/vouchers/verify?code=' . urlencode($code); ?>
    <div class="voucher-card">
      <div class="voucher-header">
        <?php if ($logoUrl): ?><img src="<?= htmlspecialchars($logoUrl) ?>" class="voucher-logo" alt="Logo"><?php endif; ?>
        <div>
          <div class="voucher-title"><?= htmlspecialchars($storeName ?: 'Voucher') ?></div>
          <div class="hint">MavicFy • <?= htmlspecialchars($v['currency_code'] ?? '') ?></div>
        </div>
      </div>
      <div class="voucher-field"><strong>Customer:</strong> <?= htmlspecialchars($c['name'] ?? '—') ?></div>
      <div class="voucher-field"><strong>Code:</strong> <span class="voucher-code"><?= htmlspecialchars($code) ?></span></div>
      <div class="voucher-field"><strong>Expiry:</strong> <?= htmlspecialchars($v['expiry_date'] ?? '') ?></div>
      <?php if ($showAmount): ?>
        <div class="voucher-field"><strong>Amount:</strong> <?= number_format((float)($v['value'] ?? 0), 2) ?> <?= htmlspecialchars($v['currency_code'] ?? '') ?></div>
      <?php endif; ?>
      <div class="voucher-field"><strong>Top-up:</strong> ____________</div>
      <div class="voucher-field"><strong>Verify:</strong> <a href="<?= htmlspecialchars($verifyUrl) ?>" target="_blank"><?= htmlspecialchars($verifyUrl) ?></a></div>
      <div class="qr-box" data-verify-url="<?= htmlspecialchars($verifyUrl) ?>"></div>
    </div>
  <?php endforeach; ?>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  document.querySelectorAll('.qr-box').forEach(function(box) {
    var url = box.getAttribute('data-verify-url');
    new QRCode(box, { text: url, width: 96, height: 96 });
  });
</script>
