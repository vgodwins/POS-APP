<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">Compose Message</div>
      <div class="card-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/messages/create">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <div class="mb-3">
            <label class="form-label">Recipient</label>
            <select class="form-select" name="recipient_id">
              <option value="">Broadcast to store</option>
              <?php foreach (($users ?? []) as $u): ?>
                <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['name'] ?? '') ?> (<?= htmlspecialchars($u['email'] ?? '') ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Or Send to Customer</label>
            <select class="form-select" name="recipient_customer_id">
              <option value="">— Select customer (optional) —</option>
              <?php foreach (($customers ?? []) as $c): ?>
                <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name'] ?? '') ?><?= !empty($c['email']) ? ' (' . htmlspecialchars($c['email']) . ')' : '' ?></option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">Admins/owners/managers can send messages to customers. Customers will receive an email notification.</small>
          </div>
          <div class="mb-3">
            <label class="form-label">Subject (optional)</label>
            <input type="text" class="form-control" name="subject" placeholder="Subject">
          </div>
          <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea class="form-control" name="body" rows="6" placeholder="Type your message" required></textarea>
          </div>
          <div class="d-flex justify-content-between">
            <a href="/messages" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary" type="submit">Send</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
