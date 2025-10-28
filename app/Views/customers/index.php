<?php
use App\Core\Config;
$currency = Config::get('defaults')['currency_symbol'] ?? '₦';
$list = $customers ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Customers</h3>
  <div>
    <a href="/customers/create" class="btn btn-primary">Add Customer</a>
    <a href="/products" class="btn btn-outline-secondary">Back to Products</a>
  </div>
  </div>
<div class="mb-3">
  <label class="form-label">Search</label>
  <input type="text" id="customerSearch" class="form-control" placeholder="Type name, phone, or email">
  <small class="text-muted">Quickly filter the list by any field.</small>
</div>
<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Name</th>
      <th>Phone</th>
      <th>Email</th>
      <th style="width: 160px;">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($list as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['name'] ?? '') ?></td>
        <td><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
        <td><?= htmlspecialchars($c['email'] ?? '—') ?></td>
        <td>
          <a href="/customers/edit?id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
          <form method="post" action="/customers/delete" class="d-inline" onsubmit="return confirm('Delete this customer?');">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($list)): ?>
      <tr><td colspan="4" class="text-muted">No customers yet</td></tr>
    <?php endif; ?>
  </tbody>
</table>
<script>
  const search = document.getElementById('customerSearch');
  function filterRows() {
    const q = (search.value || '').trim().toLowerCase();
    document.querySelectorAll('tbody tr').forEach(tr => {
      const txt = tr.innerText.toLowerCase();
      tr.style.display = txt.includes(q) ? '' : 'none';
    });
  }
  search.addEventListener('input', filterRows);
  search.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); filterRows(); }});
</script>
