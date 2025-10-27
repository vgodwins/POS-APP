<div class="row">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header">Bulk Generate Vouchers</div>
      <div class="card-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" action="/vouchers/bulk_save">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Count</label>
              <input type="number" min="1" max="500" class="form-control" name="count" value="10" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Value</label>
              <input type="number" step="0.01" class="form-control" name="value" required>
            </div>
            <div class="col-md-5">
              <label class="form-label">Expiry Date</label>
              <input type="date" class="form-control" name="expiry_date" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Code Prefix (optional)</label>
              <input type="text" class="form-control" name="prefix" placeholder="e.g., PROMO-" maxlength="28">
              <div class="form-text">Max combined code length is 32 characters.</div>
            </div>
          </div>
          <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Generate</button>
            <a href="/vouchers" class="btn btn-secondary">Back to List</a>
          </div>
        </form>
        <?php if (!empty($codes) && is_array($codes)): ?>
          <hr>
          <h6>Generated Codes</h6>
          <ul>
            <?php foreach ($codes as $c): ?>
              <li><code><?= htmlspecialchars($c) ?></code></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
