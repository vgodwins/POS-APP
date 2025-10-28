<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Edit Voucher</div>
      <div class="card-body">
        <?php if (!empty($currentCustomer)): ?>
          <div class="alert alert-info">
            <div><strong>Linked Customer:</strong> <?= htmlspecialchars($currentCustomer['name'] ?? '') ?></div>
            <?php if (!empty($currentCustomer['phone'])): ?>
              <div><strong>Phone:</strong> <?= htmlspecialchars($currentCustomer['phone']) ?></div>
            <?php endif; ?>
            <?php if (!empty($currentCustomer['email'])): ?>
              <div><strong>Email:</strong> <?= htmlspecialchars($currentCustomer['email']) ?></div>
            <?php endif; ?>
          </div>
        <?php elseif (!empty($voucher['customer_id'])): ?>
          <div class="alert alert-info">Linked Customer ID: <?= (int)$voucher['customer_id'] ?></div>
        <?php else: ?>
          <div class="alert alert-secondary">No linked customer</div>
        <?php endif; ?>
        <form method="post" action="/vouchers/update">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= (int)$voucher['id'] ?>">
          <div class="mb-3">
            <label class="form-label">Code</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($voucher['code']) ?>" disabled>
          </div>
          <div class="mb-3">
            <label class="form-label">Value</label>
            <input type="number" step="0.01" class="form-control" name="value" value="<?= number_format((float)$voucher['value'], 2, '.', '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Currency</label>
            <input type="text" maxlength="3" class="form-control" name="currency_code" value="<?= htmlspecialchars($voucher['currency_code']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Customer (optional)</label>
            <select class="form-select" name="customer_id">
              <option value="">None</option>
              <?php $cid = $voucher['customer_id'] ?? null; ?>
              <?php foreach (($customers ?? []) as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= ($cid == $c['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['name']) ?><?= $c['phone'] ? (' - ' . htmlspecialchars($c['phone'])) : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Expiry Date</label>
            <input type="date" class="form-control" name="expiry_date" value="<?= htmlspecialchars($voucher['expiry_date']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Top-up Amount</label>
            <input type="number" step="0.01" class="form-control" name="top_up_value" value="0">
            <div class="form-text">Add additional credit to this voucher.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <?php $statuses = ['active','used','expired']; ?>
              <?php foreach ($statuses as $s): ?>
                <option value="<?= htmlspecialchars($s) ?>" <?= ($voucher['status'] === $s) ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($s)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn-primary" type="submit">Save Changes</button>
          <a href="/vouchers" class="btn btn-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</div>
