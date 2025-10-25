<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Add Expense</div>
      <div class="card-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/expenses/save">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <div class="mb-3">
            <label class="form-label">Category</label>
            <input type="text" class="form-control" name="category" placeholder="e.g., Utilities, Rent, Supplies" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Amount</label>
            <input type="number" step="0.01" class="form-control" name="amount" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Note</label>
            <textarea class="form-control" name="note" rows="3" placeholder="Optional details"></textarea>
          </div>
          <button class="btn btn-success" type="submit">Save Expense</button>
          <a class="btn btn-secondary" href="/expenses">Back</a>
        </form>
      </div>
    </div>
  </div>
</div>