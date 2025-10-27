<?php
$store = $store ?? [];
$env = strtolower((string)((\App\Core\Config::get('app')['env'] ?? 'development')));
?>
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">App Settings</div>
      <div class="card-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/settings/save">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Business Name</label>
              <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($store['name'] ?? '') ?>">
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Currency Code</label>
              <input type="text" class="form-control" name="currency_code" value="<?= htmlspecialchars($store['currency_code'] ?? 'NGN') ?>">
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Currency Symbol</label>
              <input type="text" class="form-control" name="currency_symbol" value="<?= htmlspecialchars($store['currency_symbol'] ?? '₦') ?>">
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Tax Rate</label>
              <input type="number" step="0.001" class="form-control" name="tax_rate" value="<?= htmlspecialchars($store['tax_rate'] ?? 0.075) ?>">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Theme</label>
              <select class="form-select" name="theme">
                <option value="light" <?= ($store['theme'] ?? 'light') === 'light' ? 'selected' : '' ?>>Light</option>
                <option value="dark" <?= ($store['theme'] ?? 'light') === 'dark' ? 'selected' : '' ?>>Dark</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Address</label>
              <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($store['address'] ?? '') ?>">
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Phone</label>
              <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($store['phone'] ?? '') ?>">
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Logo URL</label>
              <input type="text" class="form-control" name="logo_url" value="<?= htmlspecialchars($store['logo_url'] ?? '') ?>">
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Company Number</label>
              <input type="text" class="form-control" name="company_number" value="<?= htmlspecialchars($store['company_number'] ?? '') ?>" placeholder="e.g. RC1234567">
            </div>
          </div>
          <?php if (!empty($store['logo_url'])): ?>
            <div class="mb-3">
              <label class="form-label">Current Logo</label><br>
              <img src="<?= htmlspecialchars($store['logo_url']) ?>" alt="Logo" style="max-height:80px;">
            </div>
          <?php endif; ?>
          <button class="btn btn-success" type="submit">Save Settings</button>
        </form>
      </div>
    </div>
    <div class="card mt-4">
      <div class="card-header">Upload Logo</div>
      <div class="card-body">
        <form method="post" action="/settings/upload_logo" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="file" name="logo" accept="image/*" class="form-control" style="max-width: 360px;" required>
          <button type="submit" class="btn btn-primary">Upload</button>
        </form>
      </div>
    </div>
    <div class="card mt-4 border-danger">
      <div class="card-header text-danger">Danger Zone</div>
      <div class="card-body">
        <p class="mb-3">This will permanently delete all <strong>sales</strong>, <strong>expenses</strong>, <strong>vouchers</strong>, and <strong>customers</strong> for this store. Products and settings are kept. This cannot be undone.</p>
        <?php if ($env === 'production'): ?>
          <div class="alert alert-warning mb-3">Clear App Data is disabled in production.</div>
        <?php endif; ?>
        <form method="post" action="/settings/clear_data" class="d-flex gap-2 align-items-center">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="text" name="confirm_text" class="form-control" placeholder="Type CLEAR to confirm" style="max-width: 260px;" required>
          <button type="submit" class="btn btn-danger">Clear App Data</button>
        </form>
      </div>
    </div>
  </div>
</div>
