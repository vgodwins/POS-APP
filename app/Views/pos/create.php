<?php
use App\Core\Config;
$currency = Config::get('defaults')['currency_symbol'] ?? '₦';
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
            </div>
          </div>

          <hr>
          <div class="row">
            <div class="col-md-3">
              <label class="form-label">Cash</label>
              <input type="number" step="0.01" class="form-control" name="payments[cash]" value="0">
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
  const defaultTaxRate = <?= json_encode( (float)(App\Core\Config::get('defaults')['tax_rate'] ?? 0) ) ?>;
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
  function updateRow(el) {
    const tr = el.closest('tr');
    const sel = tr.querySelector('select');
    const qty = parseInt(tr.querySelector('input[name="items[][qty]"]').value || '1', 10);
    const price = parseFloat(sel.selectedOptions[0]?.getAttribute('data-price') || '0');
    const taxRate = parseFloat(sel.selectedOptions[0]?.getAttribute('data-tax') || defaultTaxRate);
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
    document.getElementById('subtotal').innerText = subtotal.toFixed(2);
    document.getElementById('tax').innerText = tax.toFixed(2);
    document.getElementById('total').innerText = (subtotal + tax).toFixed(2);
  }
</script>