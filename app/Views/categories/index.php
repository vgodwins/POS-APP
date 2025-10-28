<?php
use App\Core\Config;
$list = $categories ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Categories</h3>
  <div>
    <a href="/categories/create" class="btn btn-primary">Add Category</a>
    <a href="/products" class="btn btn-outline-secondary">Back to Products</a>
  </div>
</div>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<div class="mb-3">
  <label class="form-label">Search</label>
  <input type="text" id="categorySearch" class="form-control" placeholder="Type category name">
  <small class="text-muted">Quickly filter the list by name.</small>
  </div>
<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Name</th>
      <th style="width: 160px;">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($list as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['name'] ?? '') ?></td>
        <td>
          <a href="/categories/edit?id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
          <form method="post" action="/categories/delete" class="d-inline" onsubmit="return confirm('Delete this category?');">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <button class="btn btn-sm btn-danger" type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<script>
  const search = document.getElementById('categorySearch');
  function filterRows() {
    const q = search.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(tr => {
      const name = tr.children[0].textContent.toLowerCase();
      tr.style.display = name.includes(q) ? '' : 'none';
    });
  }
  search.addEventListener('input', filterRows);
  search.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); filterRows(); }});
</script>
