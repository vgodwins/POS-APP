<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">Add Product</div>
      <div class="card-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/products/save">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Name</label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">SKU</label>
              <input type="text" class="form-control" name="sku">
            </div>
            <div class="col-md-3">
              <label class="form-label">Barcode</label>
              <input type="text" class="form-control" name="barcode">
            </div>
            <div class="col-md-3">
              <label class="form-label">Price</label>
              <input type="number" step="0.01" class="form-control" name="price" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Cost Price</label>
              <input type="number" step="0.01" class="form-control" name="cost_price" value="0">
            </div>
            <div class="col-md-3">
              <label class="form-label">Stock</label>
              <input type="number" class="form-control" name="stock" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                <option value="valid">Valid</option>
                <option value="expired">Expired</option>
                <option value="damaged">Damaged</option>
                <option value="returned">Returned</option>
              </select>
            </div>
          </div>
          <div class="mt-3">
            <button class="btn btn-success" type="submit">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
