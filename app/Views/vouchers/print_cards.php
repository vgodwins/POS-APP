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
    /* Simplify visuals for print */
    .voucher-card { box-shadow: none !important; transform: none !important; }
    .voucher-card::before, .voucher-card::after { display: none !important; }
  }
  .print-actions { margin-bottom: 12px; }
  .cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
  .voucher-card {
    position: relative;
    border-radius: 12px;
    padding: 14px 14px 18px 14px;
    overflow: hidden;
    color: #fff;
    background: linear-gradient(135deg, var(--bg-start, #4158D0), var(--bg-end, #C850C0));
    box-shadow: 0 6px 18px rgba(16,24,40,0.18);
    transition: transform .18s ease, box-shadow .18s ease;
  }
  .voucher-card::before {
    /* Interactive blend spotlight */
    content: "";
    position: absolute; inset: 0;
    background: radial-gradient(600px circle at var(--mx, 50%) var(--my, 50%), rgba(255,255,255,0.18), transparent 40%);
    mix-blend-mode: soft-light;
    opacity: 0; transition: opacity .15s ease;
    pointer-events: none;
  }
  .voucher-card:hover { transform: translateY(-2px); box-shadow: 0 9px 24px rgba(16,24,40,0.22); }
  .voucher-card:hover::before { opacity: 1; }
  .voucher-card.selected { outline: 2px solid rgba(255,255,255,0.75); outline-offset: -2px; }
  .voucher-card.selected::after {
    content: "✓";
    position: absolute; top: 10px; left: 10px;
    width: 24px; height: 24px; border-radius: 50%;
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.7);
    color: #fff; font-weight: 700; font-size: 14px;
    display: grid; place-items: center;
  }
  .voucher-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
  .voucher-logo { height: 28px; width: auto; border-radius: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.25); }
  .voucher-title { font-weight: 700; letter-spacing: 0.2px; }
  .hint { font-size: 0.85rem; color: rgba(255,255,255,0.8); }
  .voucher-field { margin: 6px 0; }
  .voucher-field strong { color: rgba(255,255,255,0.92); }
  .voucher-code { font-family: monospace; font-size: 1.1rem; background: rgba(255,255,255,0.16); padding: 2px 6px; border-radius: 6px; }
  .qr-box { position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.96); padding: 4px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.2); }
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
    <?php
      $v = $entry['voucher'] ?? []; $c = $entry['customer'] ?? null; $code = $v['code'] ?? '';
      $verifyUrl = ($host ? rtrim($host, '/') : '') . '/vouchers/verify?code=' . urlencode($code);
      // Derive a pleasant gradient per card from code hash
      $h = (int)(crc32((string)$code) % 360);
      $start = "hsl({$h}, 72%, 55%)";
      $end = "hsl(" . (($h + 28) % 360) . ", 82%, 45%)";
      $styleVars = "--bg-start: {$start}; --bg-end: {$end};";
    ?>
    <div class="voucher-card" style="<?= htmlspecialchars($styleVars) ?>">
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
  // Interactive blend: follow cursor to create a soft-light spotlight and allow click-to-select
  document.querySelectorAll('.voucher-card').forEach(function(card) {
    card.addEventListener('mousemove', function(e) {
      var rect = card.getBoundingClientRect();
      var x = e.clientX - rect.left;
      var y = e.clientY - rect.top;
      card.style.setProperty('--mx', x + 'px');
      card.style.setProperty('--my', y + 'px');
    });
    card.addEventListener('click', function() {
      card.classList.toggle('selected');
    });
  });
</script>
