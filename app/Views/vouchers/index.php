<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Vouchers</h3>
  <div class="d-flex gap-2 align-items-center">
    <input type="text" id="voucherSearch" class="form-control" placeholder="Search code or value" style="max-width: 240px;">
    <a href="/vouchers/create" class="btn btn-primary">Create Voucher</a>
  </div>
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
<script>
  const vSearch = document.getElementById('voucherSearch');
  function filterVouchers() {
    const q = (vSearch.value || '').trim().toLowerCase();
    document.querySelectorAll('tbody tr').forEach(tr => {
      const txt = tr.innerText.toLowerCase();
      tr.style.display = txt.includes(q) ? '' : 'none';
    });
  }
  vSearch.addEventListener('input', filterVouchers);
  vSearch.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); filterVouchers(); }});
</script>
