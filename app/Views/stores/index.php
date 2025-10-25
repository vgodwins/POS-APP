<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Stores</h3>
  <a href="/stores/create" class="btn btn-primary">Create Store</a>
</div>
<table class="table table-striped">
  <thead>
    <tr>
      <th>Name</th>
      <th>Currency</th>
      <th>Tax Rate</th>
      <th>Theme</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach (($stores ?? []) as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['name']) ?></td>
        <td><?= htmlspecialchars($s['currency_symbol']) ?> (<?= htmlspecialchars($s['currency_code']) ?>)</td>
        <td><?= number_format((float)$s['tax_rate'], 3) ?></td>
        <td><?= htmlspecialchars($s['theme']) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>