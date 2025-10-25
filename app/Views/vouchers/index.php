<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Vouchers</h3>
  <a href="/vouchers/create" class="btn btn-primary">Create Voucher</a>
</div>
<table class="table table-striped">
  <thead>
    <tr>
      <th>Code</th>
      <th>Value</th>
      <th>Currency</th>
      <th>Expiry</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach (($vouchers ?? []) as $v): ?>
      <tr>
        <td><?= htmlspecialchars($v['code']) ?></td>
        <td><?= number_format((float)$v['value'], 2) ?></td>
        <td><?= htmlspecialchars($v['currency_code']) ?></td>
        <td><?= htmlspecialchars($v['expiry_date']) ?></td>
        <td><?= htmlspecialchars($v['status']) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>