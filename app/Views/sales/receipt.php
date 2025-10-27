<?php
use App\Core\Config;
$currency = $store['currency_symbol'] ?? (Config::get('defaults')['currency_symbol'] ?? '₦');

// Helper to build a centered line for typical 40-char mini printers
$width = 40;
$center = function(string $text) use ($width) {
    $t = trim($text);
    $len = strlen($t);
    if ($len >= $width) return $t; // no padding if longer
    $pad = intdiv($width - $len, 2);
    return str_repeat(' ', $pad) . $t;
};

// Header lines
$storeName = (string)($store['name'] ?? 'Receipt');
$headerLines = [];
$headerLines[] = $center($storeName);
if (!empty($store['company_number'])) { $headerLines[] = $center('RC: ' . (string)$store['company_number']); }
if (!empty($store['phone'])) { $headerLines[] = $center((string)$store['phone']); }
if (!empty($store['address'])) { $headerLines[] = $center((string)$store['address']); }
$headerLines[] = str_repeat('-', $width);
$headerLines[] = $center('#' . (int)($sale['id'] ?? 0) . ' • ' . (string)($sale['created_at'] ?? ''));
$headerLines[] = str_repeat('-', $width);

// Items lines
$itemLines = [];
foreach (($items ?? []) as $it) {
    $name = substr((string)$it['name'], 0, 20);
    $qty = (int)$it['qty'];
    $price = number_format((float)$it['price'], 2);
    $tax = number_format((float)$it['tax'], 2);
    $line = ($it['price'] * $it['qty']) + $it['tax'];
    $lineTotal = number_format((float)$line, 2);
    // Format: NAME(QTY) PRICE TAX TOTAL
    $left = sprintf('%s x%d', $name, $qty);
    $right = sprintf('%s%s %s %s', $currency, $price, $tax, $lineTotal);
    // Trim or pad to width
    $l = $left;
    $r = $right;
    $space = max(1, $width - strlen($l) - strlen($r));
    $itemLines[] = $l . str_repeat(' ', $space) . $r;
}

// Totals
$totals = [];
$totals[] = str_repeat('-', $width);
$totals[] = str_pad('Subtotal', 10) . str_pad($currency . number_format((float)($sale['subtotal'] ?? 0), 2), $width - 10, ' ', STR_PAD_LEFT);
$totals[] = str_pad('Tax', 10) . str_pad($currency . number_format((float)($sale['tax_total'] ?? 0), 2), $width - 10, ' ', STR_PAD_LEFT);
$totals[] = str_pad('Total', 10) . str_pad($currency . number_format((float)($sale['total_amount'] ?? 0), 2), $width - 10, ' ', STR_PAD_LEFT);
$totals[] = str_repeat('-', $width);

// Payments
$payLines = [];
foreach (($payments ?? []) as $p) {
    $method = ucwords(str_replace('_', ' ', (string)$p['method']));
    $amt = $currency . number_format((float)$p['amount'], 2);
    $payLines[] = str_pad($method, 12) . str_pad($amt, $width - 12, ' ', STR_PAD_LEFT);
}

// Footer
$footer = [];
$footer[] = $center('Thank you for your purchase!');

$text = implode("\n", array_merge($headerLines, $itemLines, $totals));
if ($payLines) {
    $text .= "\n" . $center('Payments') . "\n" . implode("\n", $payLines);
}
$text .= "\n" . str_repeat('-', $width) . "\n" . implode("\n", $footer) . "\n";
?>
<div class="card">
  <div class="card-header text-center">
    <strong>Receipt</strong>
  </div>
  <div class="card-body">
    <pre style="font-family: monospace; font-size: 13px; line-height: 1.4; white-space: pre;">
<?= htmlspecialchars($text) ?>
    </pre>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary" onclick="window.print()">Print</button>
      <a class="btn btn-primary" href="/pos">New Sale</a>
    </div>
  </div>
</div>
