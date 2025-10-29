<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Products</h3>
  <div>
    <?php if (\App\Core\Auth::hasRole('admin') || \App\Core\Auth::hasRole('owner') || \App\Core\Auth::hasRole('manager')): ?>
      <a href="/products/upload" class="btn btn-secondary">Bulk Upload CSV</a>
      <a href="/products/create" class="btn btn-primary">Add Product</a>
      <a href="/products/export.csv<?= (isset($selectedCategoryId) && $selectedCategoryId) ? ('?category_id=' . (int)$selectedCategoryId) : '' ?>" class="btn btn-outline-secondary">Export Inventory CSV</a>
      <a href="/categories/create" class="btn btn-outline-secondary">Add New Category</a>
    <?php endif; ?>
  </div>
</div>
<div class="mb-3">
  <div class="row g-2">
    <div class="col-md-6">
      <label class="form-label">Search / Scan Barcode</label>
      <input type="text" id="productSearch" class="form-control" placeholder="Type name, SKU, or scan barcode">
    </div>
    <div class="col-md-3">
      <label class="form-label">Status</label>
      <select id="statusFilter" class="form-select">
        <option value="">All</option>
        <option value="valid">Valid</option>
        <option value="expired">Expired</option>
        <option value="damaged">Damaged</option>
        <option value="returned">Returned</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Category</label>
      <select id="categoryFilter" class="form-select">
        <option value="">All</option>
        <?php foreach (($categories ?? []) as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= (isset($selectedCategoryId) && $selectedCategoryId == $c['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
</div>
<?php $threshold = $lowThreshold ?? 5; $s = $summary ?? ['totalProducts'=>0,'totalStock'=>0,'valid'=>0,'expired'=>0,'damaged'=>0,'returned'=>0,'lowStockCount'=>0]; ?>
<div class="row mb-3">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-3 align-items-center">
          <div><strong>Total Products:</strong> <?= (int)$s['totalProducts'] ?></div>
          <div><strong>Total Stock Units:</strong> <?= (int)$s['totalStock'] ?></div>
          <div><strong>Valid:</strong> <?= (int)$s['valid'] ?></div>
          <div><strong>Expired:</strong> <?= (int)$s['expired'] ?></div>
          <div><strong>Damaged:</strong> <?= (int)$s['damaged'] ?></div>
          <div><strong>Returned:</strong> <?= (int)$s['returned'] ?></div>
          <div><strong>Low Stock ≤ <?= (int)$threshold ?>:</strong> <?= (int)$s['lowStockCount'] ?></div>
        </div>
      </div>
    </div>
  </div>
</div>
<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Name</th>
      <th>SKU</th>
      <th>Barcode</th>
      <th>Price</th>
      <th>Cost Price</th>
      <th>Stock</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach (($products ?? []) as $p): ?>
      <?php $isLow = ((int)($p['stock'] ?? 0) <= (int)$threshold); ?>
      <tr<?= $isLow ? ' class="table-warning"' : '' ?>>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td><?= htmlspecialchars($p['sku']) ?></td>
        <td><?= htmlspecialchars($p['barcode']) ?></td>
        <td><?= number_format((float)$p['price'], 2) ?></td>
        <td><?= isset($p['cost_price']) ? number_format((float)$p['cost_price'], 2) : '—' ?></td>
        <td>
          <?php if ($isLow): ?>
            <span class="badge bg-warning text-dark">Low (<?= (int)$p['stock'] ?>)</span>
          <?php else: ?>
            <?= (int)$p['stock'] ?>
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($p['status'] ?? 'valid') ?></td>
        <td>
          <?php if (\App\Core\Auth::hasRole('admin') || \App\Core\Auth::hasRole('owner') || \App\Core\Auth::hasRole('manager')): ?>
            <a href="/products/edit?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<script>
  const search = document.getElementById('productSearch');
  const statusFilter = document.getElementById('statusFilter');
  const categoryFilter = document.getElementById('categoryFilter');
  function filterRows() {
    const q = (search.value || '').trim().toLowerCase();
    const s = (statusFilter.value || '').trim().toLowerCase();
    document.querySelectorAll('tbody tr').forEach(tr => {
      const txt = tr.innerText.toLowerCase();
      const matchesText = txt.includes(q);
      const statusCell = tr.children[6]?.innerText.toLowerCase() || '';
      const matchesStatus = !s || statusCell === s;
      tr.style.display = (matchesText && matchesStatus) ? '' : 'none';
    });
  }
  search.addEventListener('input', filterRows);
  search.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); filterRows(); }});
  statusFilter.addEventListener('change', filterRows);
  categoryFilter.addEventListener('change', () => {
    const catId = categoryFilter.value;
    const url = new URL(window.location.href);
    if (catId) { url.searchParams.set('category_id', catId); } else { url.searchParams.delete('category_id'); }
    window.location.href = url.toString();
  });
</script>
