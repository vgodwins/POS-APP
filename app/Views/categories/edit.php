<?php $err = $error ?? null; $c = $category ?? ['id'=>0,'name'=>'']; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Edit Category</h3>
  <a class="btn btn-outline-secondary" href="/categories">Back</a>
  </div>
<?php if ($err): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>
<div class="card">
  <div class="card-body">
    <form method="post" action="/categories/update" class="row g-3">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int)($c['id'] ?? 0) ?>">
      <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($c['name'] ?? '') ?>" required>
      </div>
      <div class="col-12">
        <button class="btn btn-primary" type="submit">Save Changes</button>
        <a class="btn btn-outline-secondary" href="/categories">Cancel</a>
      </div>
    </form>
  </div>
  <div class="card-footer">
    <form method="post" action="/categories/delete" onsubmit="return confirm('Delete this category?');">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int)($c['id'] ?? 0) ?>">
      <button type="submit" class="btn btn-outline-danger">Delete Category</button>
    </form>
  </div>
</div>
