<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Edit Expense</div>
      <div class="card-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/expenses/update">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= (int)($expense['id'] ?? 0) ?>">
          <div class="mb-3">
            <label class="form-label">Category</label>
            <input type="text" class="form-control" name="category" value="<?= htmlspecialchars($expense['category'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Amount</label>
            <input type="number" step="0.01" class="form-control" name="amount" value="<?= htmlspecialchars($expense['amount'] ?? '0') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Note</label>
            <textarea class="form-control" name="note" rows="3" placeholder="Optional details"><?= htmlspecialchars($expense['note'] ?? '') ?></textarea>
          </div>
          <button class="btn btn-success" type="submit">Update Expense</button>
          <a class="btn btn-secondary" href="/expenses">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</div>
