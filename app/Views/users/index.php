<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Users</h3>
  <a href="/users/create" class="btn btn-primary">Add User</a>
</div>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Name</th>
      <th>Email</th>
      <th>Roles</th>
      <th>Store</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach (($users ?? []) as $u): ?>
    <tr>
      <td><?= htmlspecialchars($u['name']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><?= htmlspecialchars(is_array($u['roles']) ? implode(',', $u['roles']) : ($u['roles'] ?? '')) ?></td>
      <td><?= htmlspecialchars($u['store_name'] ?? '') ?></td>
      <td>
        <a href="/users/edit?id=<?= (int)$u['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
        <form method="post" action="/users/delete" class="d-inline" onsubmit="return confirm('Delete this user?');">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
          <button class="btn btn-sm btn-danger" type="submit">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>