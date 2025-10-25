<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">Create Store</div>
      <div class="card-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/stores/save">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Store Name</label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Currency Code</label>
              <select class="form-select" name="currency_code">
                <option value="NGN">NGN</option>
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
                <option value="GBP">GBP</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Currency Symbol</label>
              <input type="text" class="form-control" name="currency_symbol" value="₦">
            </div>
            <div class="col-md-3">
              <label class="form-label">Tax Rate</label>
              <input type="number" step="0.001" class="form-control" name="tax_rate" value="0.075">
            </div>
            <div class="col-md-3">
              <label class="form-label">Theme</label>
              <select class="form-select" name="theme">
                <option value="light">Light</option>
                <option value="dark">Dark</option>
              </select>
            </div>
          </div>
          <div class="mt-3">
            <button class="btn btn-success" type="submit">Save Store</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>