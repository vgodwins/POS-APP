<?php
use App\Core\Config;
$currency = Config::get('defaults')['currency_symbol'] ?? '₦';
$storeTaxRate = isset($store['tax_rate']) ? (float)$store['tax_rate'] : (float)(Config::get('defaults')['tax_rate'] ?? 0.0);
?>
<div class="row">
  <div class="col-md-8">
    <div class="card mb-3">
      <div class="card-header">New Sale</div>
      <div class="card-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/pos/checkout" id="saleForm">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Scan Barcode</label>
              <input type="text" class="form-control" id="scanBarcode" placeholder="Scan barcode here">
              <div class="form-text" id="scanStatus"></div>
            </div>
          </div>

          <table class="table" id="itemsTable">
            <thead>
              <tr><th>Product</th><th>Qty</th><th>Price</th><th>Line Total</th><th></th></tr>
            </thead>
            <tbody></tbody>
          </table>
          <button type="button" class="btn btn-outline-primary" onclick="addItemRow()">Add Item</button>

          <div class="row mt-3">
            <div class="col-md-4">
              <label class="form-label">Voucher Code</label>
              <input type="text" class="form-control" name="voucher_code" placeholder="Optional">
              <div class="form-text" id="voucherStatus"></div>
            </div>
          </div>

          <hr>
          <div class="row">
            <div class="col-md-3">
              <label class="form-label">Cash</label>
              <input type="number" step="0.01" class="form-control" name="payments[cash]" value="0" readonly>
            </div>
            <div class="col-md-3">
              <label class="form-label">Card</label>
              <input type="number" step="0.01" class="form-control" name="payments[card]" value="0">
            </div>
            <div class="col-md-3">
              <label class="form-label">Bank Transfer</label>
              <input type="number" step="0.01" class="form-control" name="payments[bank_transfer]" value="0">
            </div>
          </div>

          <div class="mt-3">
            <h5>Subtotal: <span id="subtotal">0.00</span></h5>
            <h5>Tax: <span id="tax">0.00</span></h5>
            <h4>Total: <?= htmlspecialchars($currency) ?><span id="total">0.00</span></h4>
          </div>
          <button class="btn btn-success" type="submit">Checkout</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  const products = <?= json_encode($products ?? []) ?>;
  const storeTaxRate = <?= json_encode($storeTaxRate) ?>;
  let voucherValue = 0.0;

  function addItemRow() {
    const tbody = document.querySelector('#itemsTable tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select name="items[][product_id]" class="form-select" onchange="updateRow(this)">
          <option value="">Select product</option>
          ${products.map(p => `<option value="${p.id}" data-price="${p.price}" data-tax="${p.tax_rate}">${p.name}</option>`).join('')}
        </select>
      </td>
      <td><input type="number" name="items[][qty]" class="form-control" value="1" min="1" onchange="updateRow(this)"></td>
      <td class="price">0.00</td>
      <td class="line">0.00</td>
      <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); recalcTotals();">Remove</button></td>
    `;
    tbody.appendChild(tr);
  }

  function addItemRowWithProduct(pid) {
    addItemRow();
    const tr = document.querySelector('#itemsTable tbody tr:last-child');
    const sel = tr.querySelector('select');
    sel.value = String(pid);
    updateRow(sel);
  }

  function updateRow(el) {
    const tr = el.closest('tr');
    const sel = tr.querySelector('select');
    const qty = parseInt(tr.querySelector('input[name="items[][qty]"]').value || '1', 10);
    const price = parseFloat(sel.selectedOptions[0]?.getAttribute('data-price') || '0');
    const taxRate = storeTaxRate;
    tr.querySelector('.price').innerText = price.toFixed(2);
    const lineSub = price * qty;
    const lineTax = lineSub * taxRate;
    tr.dataset.lineSub = lineSub;
    tr.dataset.lineTax = lineTax;
    tr.querySelector('.line').innerText = (lineSub + lineTax).toFixed(2);
    recalcTotals();
  }

  function recalcTotals() {
    let subtotal = 0, tax = 0;
    document.querySelectorAll('#itemsTable tbody tr').forEach(tr => {
      subtotal += parseFloat(tr.dataset.lineSub || '0');
      tax += parseFloat(tr.dataset.lineTax || '0');
    });
    const total = subtotal + tax;
    document.getElementById('subtotal').innerText = subtotal.toFixed(2);
    document.getElementById('tax').innerText = tax.toFixed(2);
    document.getElementById('total').innerText = total.toFixed(2);
    updatePayments(total);
  }

  function updatePayments(total) {
    const cardEl = document.querySelector('input[name="payments[card]"]');
    const bankEl = document.querySelector('input[name="payments[bank_transfer]"]');
    const cashEl = document.querySelector('input[name="payments[cash]"]');
    const card = parseFloat(cardEl.value || '0') || 0;
    const bank = parseFloat(bankEl.value || '0') || 0;
    const nonCash = card + bank + voucherValue;
    let cash = 0;
    if (nonCash >= total) {
      // clamp non-cash to fit total
      let excess = nonCash - total;
      if (excess > 0) {
        if (bank >= excess) { bankEl.value = (bank - excess).toFixed(2); }
        else { excess -= bank; bankEl.value = '0.00'; cardEl.value = Math.max(0, card - excess).toFixed(2); }
      }
      cash = 0;
    } else {
      cash = total - nonCash;
    }
    cashEl.value = cash.toFixed(2);
  }

  // Voucher validation
  const voucherInput = document.querySelector('input[name="voucher_code"]');
  let voucherTimer = null;
  function fetchVoucher() {
    const code = (voucherInput.value || '').trim();
    const status = document.getElementById('voucherStatus');
    if (!code) { voucherValue = 0; status.textContent = ''; recalcTotals(); return; }
    status.textContent = 'Checking voucher…';
    fetch(`/vouchers/validate?code=${encodeURIComponent(code)}`)
      .then(r => r.json())
      .then(js => {
        if (js && js.ok) {
          voucherValue = parseFloat(js.value || '0') || 0;
          status.textContent = `Voucher applied: <?= htmlspecialchars($currency) ?>${voucherValue.toFixed(2)}`;
        } else {
          voucherValue = 0;
          status.textContent = 'Voucher invalid or expired';
        }
        recalcTotals();
      })
      .catch(() => { voucherValue = 0; status.textContent = 'Voucher check failed'; recalcTotals(); });
  }
  voucherInput.addEventListener('input', () => { clearTimeout(voucherTimer); voucherTimer = setTimeout(fetchVoucher, 300); });
  voucherInput.addEventListener('blur', fetchVoucher);

  // Card/Bank inputs re-calc cash automatically
  document.querySelector('input[name="payments[card]"]').addEventListener('input', () => recalcTotals());
  document.querySelector('input[name="payments[bank_transfer]"]').addEventListener('input', () => recalcTotals());

  // Barcode scanning
  const scanInput = document.getElementById('scanBarcode');
  scanInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const code = (scanInput.value || '').trim();
      const status = document.getElementById('scanStatus');
      if (!code) return;
      const p = products.find(pr => (pr.barcode || '') === code);
      if (p) {
        addItemRowWithProduct(p.id);
        status.textContent = `Added: ${p.name}`;
        scanInput.value = '';
      } else {
        status.textContent = 'No product found for this barcode';
      }
    }
  });
</script>
