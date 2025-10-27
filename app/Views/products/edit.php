<?php $p = $product ?? []; ?>
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">Edit Product</div>
      <div class="card-body">
        <form method="post" action="/products/update">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= (int)($p['id'] ?? 0) ?>">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Name</label>
              <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($p['name'] ?? '') ?>" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">SKU</label>
              <input type="text" class="form-control" name="sku" value="<?= htmlspecialchars($p['sku'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Barcode</label>
              <input type="text" class="form-control" name="barcode" value="<?= htmlspecialchars($p['barcode'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Price</label>
              <input type="number" step="0.01" class="form-control" name="price" value="<?= htmlspecialchars($p['price'] ?? '0') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Cost Price</label>
              <input type="number" step="0.01" class="form-control" name="cost_price" value="<?= htmlspecialchars($p['cost_price'] ?? '0') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Stock</label>
              <input type="number" class="form-control" name="stock" value="<?= htmlspecialchars($p['stock'] ?? '0') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                <?php $s = ($p['status'] ?? 'valid'); ?>
                <option value="valid" <?= $s === 'valid' ? 'selected' : '' ?>>Valid</option>
                <option value="expired" <?= $s === 'expired' ? 'selected' : '' ?>>Expired</option>
                <option value="damaged" <?= $s === 'damaged' ? 'selected' : '' ?>>Damaged</option>
                <option value="returned" <?= $s === 'returned' ? 'selected' : '' ?>>Returned</option>
              </select>
            </div>
          </div>
          <div class="mt-3">
            <button class="btn btn-success" type="submit">Update</button>
            <a class="btn btn-secondary" href="/products">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
 </div>

