<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Create Voucher</div>
      <div class="card-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/vouchers/save">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <div class="mb-3">
            <label class="form-label">Value</label>
            <input type="number" step="0.01" class="form-control" name="value" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Expiry Date</label>
            <input type="date" class="form-control" name="expiry_date" required>
          </div>
          <button class="btn btn-success" type="submit">Generate Code</button>
        </form>
      </div>
    </div>
  </div>
</div>