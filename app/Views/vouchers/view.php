<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Voucher Details</div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Code</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($voucher['code']) ?>" disabled>
        </div>
        <div class="mb-3">
          <label class="form-label">Value</label>
          <input type="text" class="form-control" value="<?= number_format((float)$voucher['value'], 2) ?>" disabled>
        </div>
        <div class="mb-3">
          <label class="form-label">Currency</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($voucher['currency_code']) ?>" disabled>
        </div>
        <div class="mb-3">
          <label class="form-label">Expiry Date</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($voucher['expiry_date']) ?>" disabled>
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($voucher['status']) ?>" disabled>
        </div>
        <hr>
        <?php $hasCid = isset($voucher['customer_id']) && $voucher['customer_id'] !== null && $voucher['customer_id'] !== ''; ?>
        <?php if ($hasCid): ?>
          <div class="alert alert-info">
            <div><strong>Linked Customer:</strong> <?= htmlspecialchars($voucher['customer_name'] ?? ('Customer #' . (int)$voucher['customer_id'])) ?></div>
            <?php if (!empty($voucher['customer_phone'] ?? '')): ?>
              <div><strong>Phone:</strong> <?= htmlspecialchars($voucher['customer_phone']) ?></div>
            <?php endif; ?>
            <?php if (!empty($voucher['customer_email'] ?? '')): ?>
              <div><strong>Email:</strong> <?= htmlspecialchars($voucher['customer_email']) ?></div>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="alert alert-secondary">No linked customer</div>
        <?php endif; ?>
        <div class="d-flex gap-2">
          <a href="/vouchers/edit?id=<?= (int)$voucher['id'] ?>" class="btn btn-secondary">Edit</a>
          <a href="/vouchers" class="btn btn-outline-secondary">Back to list</a>
        </div>
      </div>
    </div>
  </div>
</div>
