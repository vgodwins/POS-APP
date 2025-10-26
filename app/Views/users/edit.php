<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Edit User</div>
      <div class="card-body">
        <form method="post" action="/users/update">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= (int)($user['id'] ?? 0) ?>">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">New Password (leave blank to keep)</label>
            <input type="password" class="form-control" name="password">
          </div>
          <div class="mb-3">
            <label class="form-label">Store</label>
            <select name="store_id" class="form-select">
              <option value="">None</option>
              <?php foreach (($stores ?? []) as $s): ?>
                <option value="<?= (int)$s['id'] ?>" <?= ((int)($user['store_id'] ?? 0) === (int)$s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Roles</label>
            <div class="d-flex gap-3">
              <?php 
                $assigned = is_array($user['roles'] ?? null) ? $user['roles'] : (isset($user['roles']) ? explode(',', (string)$user['roles']) : []);
                foreach (($roles ?? []) as $r):
                  $checked = in_array($r, $assigned, true) ? 'checked' : '';
              ?>
                <label class="form-check-label">
                  <input class="form-check-input" type="checkbox" name="roles[]" value="<?= htmlspecialchars($r) ?>" <?= $checked ?>> <?= htmlspecialchars($r) ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="d-flex justify-content-between">
            <a href="/users" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary" type="submit">Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
