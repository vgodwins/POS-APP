<?php $err = $error ?? null; $c = $customer ?? ['id'=>0,'name'=>'','phone'=>'','email'=>'']; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Edit Customer</h3>
  <a class="btn btn-outline-secondary" href="/customers">Back</a>
</div>
<?php if ($err): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>
<div class="card">
  <div class="card-body">
    <form method="post" action="/customers/update" class="row g-3">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int)($c['id'] ?? 0) ?>">
      <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($c['name'] ?? '') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Phone</label>
        <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($c['phone'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($c['email'] ?? '') ?>">
      </div>
      <div class="col-12">
        <button class="btn btn-primary" type="submit">Save Changes</button>
        <a class="btn btn-outline-secondary" href="/customers">Cancel</a>
      </div>
    </form>
  </div>
  <div class="card-footer">
    <form method="post" action="/customers/delete" onsubmit="return confirm('Delete this customer?');">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int)($c['id'] ?? 0) ?>">
      <button type="submit" class="btn btn-outline-danger">Delete Customer</button>
    </form>
  </div>
</div>
