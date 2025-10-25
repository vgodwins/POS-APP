<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Bulk Upload Products (CSV)</div>
      <div class="card-body">
        <p>CSV columns: name,sku,barcode,price,stock</p>
        <form method="post" action="/products/upload" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="file" name="csv" accept=".csv" class="form-control" required>
          <button class="btn btn-primary mt-2" type="submit">Upload</button>
        </form>
      </div>
    </div>
  </div>
</div>