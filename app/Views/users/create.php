<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Add User</div>
      <div class="card-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/users/save">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <?php if (empty($ownerMode)): ?>
          <div class="mb-3">
            <label class="form-label">Store</label>
            <select name="store_id" class="form-select">
              <option value="">None</option>
              <?php foreach (($stores ?? []) as $s): ?>
                <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php else: ?>
            <input type="hidden" name="store_id" value="<?= (int)($ownerStoreId ?? 0) ?>">
            <div class="alert alert-info">New users will be added to your store automatically.</div>
          <?php endif; ?>
          <div class="mb-3">
            <label class="form-label">Roles</label>
            <div class="d-flex gap-3">
              <?php foreach (($roles ?? []) as $r): ?>
                <label class="form-check-label">
                  <input class="form-check-input" type="checkbox" name="roles[]" value="<?= htmlspecialchars($r) ?>"> <?= htmlspecialchars($r) ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="d-flex justify-content-between">
            <a href="/users" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary" type="submit">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
