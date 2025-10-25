<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Products</h3>
  <div>
    <a href="/products/upload" class="btn btn-secondary">Bulk Upload CSV</a>
    <a href="/products/create" class="btn btn-primary">Add Product</a>
  </div>
</div>
<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Name</th>
      <th>SKU</th>
      <th>Barcode</th>
      <th>Price</th>
      <th>Stock</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach (($products ?? []) as $p): ?>
      <tr>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td><?= htmlspecialchars($p['sku']) ?></td>
        <td><?= htmlspecialchars($p['barcode']) ?></td>
        <td><?= number_format((float)$p['price'], 2) ?></td>
        <td><?= (int)$p['stock'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>