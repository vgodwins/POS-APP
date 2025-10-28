<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Vouchers</h3>
  <div class="d-flex gap-2 align-items-center flex-wrap">
    <div class="d-flex align-items-center gap-2">
      <label class="form-label mb-0">Customer</label>
      <select id="customerFilter" class="form-select" style="max-width: 240px;">
        <option value="">All</option>
        <?php foreach (($customers ?? []) as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= (isset($selectedCustomerId) && $selectedCustomerId == $c['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['name']) ?><?= $c['phone'] ? (' - ' . htmlspecialchars($c['phone'])) : '' ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="d-flex align-items-center gap-2">
      <label class="form-label mb-0">Link</label>
      <select id="linkFilter" class="form-select" style="max-width: 200px;">
        <option value="" <?= empty($selectedLinked) ? 'selected' : '' ?>>All</option>
        <option value="1" <?= (isset($selectedLinked) && $selectedLinked === '1') ? 'selected' : '' ?>>Linked only</option>
        <option value="0" <?= (isset($selectedLinked) && $selectedLinked === '0') ? 'selected' : '' ?>>Unlinked only</option>
      </select>
    </div>
    <div class="d-flex align-items-center gap-2">
      <label class="form-label mb-0">Sort</label>
      <select id="sortFilter" class="form-select" style="max-width: 220px;">
        <option value="" <?= empty($selectedSort) ? 'selected' : '' ?>>Newest first</option>
        <option value="expiry_asc" <?= (isset($selectedSort) && $selectedSort === 'expiry_asc') ? 'selected' : '' ?>>Expiry soonest</option>
        <option value="expiry_desc" <?= (isset($selectedSort) && $selectedSort === 'expiry_desc') ? 'selected' : '' ?>>Expiry latest</option>
        <option value="value_desc" <?= (isset($selectedSort) && $selectedSort === 'value_desc') ? 'selected' : '' ?>>Value high → low</option>
        <option value="value_asc" <?= (isset($selectedSort) && $selectedSort === 'value_asc') ? 'selected' : '' ?>>Value low → high</option>
      </select>
    </div>
    <div class="form-check ms-2">
      <input class="form-check-input" type="checkbox" id="defaultUnlinked">
      <label class="form-check-label" for="defaultUnlinked">Default to Unlinked only</label>
    </div>
    <input type="text" id="voucherSearch" class="form-control" placeholder="Search code or value" style="max-width: 240px;">
    <a href="/vouchers/create" class="btn btn-primary">Create Voucher</a>
    <a href="/vouchers/bulk" class="btn btn-outline-primary">Bulk Generate</a>
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
      <th>Customer</th>
      <th>Actions</th>
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
        <?php
          $hasCid = isset($v['customer_id']) && $v['customer_id'] !== null && $v['customer_id'] !== '';
          $tip = '';
          if ($hasCid) {
            $nm = $v['customer_name'] ?? ('Customer #' . (int)$v['customer_id']);
            $ph = $v['customer_phone'] ?? '';
            $em = $v['customer_email'] ?? '';
            $pieces = array_filter([$nm, $ph, $em], fn($x) => trim((string)$x) !== '');
            $tip = implode(' | ', $pieces);
          }
        ?>
        <td title="<?= htmlspecialchars($tip) ?>">
          <?php if ($hasCid): ?>
            <?= htmlspecialchars($v['customer_name'] ?? ('Customer #' . (int)$v['customer_id'])) ?>
            <?php if (!empty($v['customer_phone'] ?? '')): ?>
              <span class="text-muted small">(<?= htmlspecialchars($v['customer_phone']) ?>)</span>
            <?php endif; ?>
          <?php else: ?>
            —
          <?php endif; ?>
        </td>
        <td>
          <a href="/vouchers/edit?id=<?= (int)$v['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
          <a href="/vouchers/view?id=<?= (int)$v['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
        </td>
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
  const customerFilter = document.getElementById('customerFilter');
  customerFilter.addEventListener('change', () => {
    const cid = customerFilter.value;
    const url = new URL(window.location.href);
    if (cid) { url.searchParams.set('customer_id', cid); } else { url.searchParams.delete('customer_id'); }
    window.location.href = url.toString();
  });
  const linkFilter = document.getElementById('linkFilter');
  linkFilter.addEventListener('change', () => {
    const val = linkFilter.value;
    const url = new URL(window.location.href);
    if (val === '1' || val === '0') { url.searchParams.set('linked', val); } else { url.searchParams.delete('linked'); }
    window.location.href = url.toString();
  });
  const sortFilter = document.getElementById('sortFilter');
  sortFilter.addEventListener('change', () => {
    const val = sortFilter.value;
    const url = new URL(window.location.href);
    if (val) { url.searchParams.set('sort', val); } else { url.searchParams.delete('sort'); }
    window.location.href = url.toString();
  });
  const defaultUnlinked = document.getElementById('defaultUnlinked');
  // Initialize checkbox from localStorage
  const pref = localStorage.getItem('vouchers_default_linked');
  defaultUnlinked.checked = (pref === '0');
  // Apply default on first load if no explicit linked param present
  (function applyDefaultIfNeeded(){
    const url = new URL(window.location.href);
    const hasLinkedParam = url.searchParams.has('linked');
    if (!hasLinkedParam && pref === '0') {
      url.searchParams.set('linked', '0');
      window.location.href = url.toString();
    }
  })();
  defaultUnlinked.addEventListener('change', () => {
    const url = new URL(window.location.href);
    if (defaultUnlinked.checked) {
      localStorage.setItem('vouchers_default_linked', '0');
      url.searchParams.set('linked', '0');
    } else {
      localStorage.removeItem('vouchers_default_linked');
      // Do not force a linked value; keep current selection unless it was defaulted
      if (url.searchParams.get('linked') === '0' && (!pref || pref === '0')) {
        url.searchParams.delete('linked');
      }
    }
    window.location.href = url.toString();
  });
</script>
