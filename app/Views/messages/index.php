<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Messages</h3>
  <div>
    <a href="/messages/create" class="btn btn-primary">Compose</a>
    <a href="/messages<?= !empty($unreadOnly) ? '' : '?unread_only=1' ?>" class="btn btn-outline-secondary"><?= !empty($unreadOnly) ? 'Show All' : 'Show Unread' ?></a>
  </div>
  </div>
<?php if (empty($messages)): ?>
  <div class="alert alert-info">No messages found.</div>
<?php else: ?>
  <div class="list-group">
    <?php foreach ($messages as $m): ?>
      <div class="list-group-item">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="badge <?= strtolower($m['status'] ?? '') === 'unread' ? 'bg-warning text-dark' : 'bg-success' ?>"><?= htmlspecialchars(ucfirst($m['status'] ?? '')) ?></span>
            <strong class="ms-2"><?= htmlspecialchars($m['subject'] ?? (substr((string)($m['body'] ?? ''), 0, 40) ?: 'Message')) ?></strong>
          </div>
          <small class="text-muted">From <?= htmlspecialchars($m['sender_name'] ?? 'Unknown') ?> • <?= htmlspecialchars($m['created_at'] ?? '') ?></small>
        </div>
        <div class="mt-2"><?= nl2br(htmlspecialchars($m['body'] ?? '')) ?></div>
        <div class="mt-2 d-flex gap-2">
          <?php $rootId = !empty($m['parent_id']) ? (int)$m['parent_id'] : (int)$m['id']; ?>
          <a class="btn btn-sm btn-outline-secondary" href="/messages/view?id=<?= $rootId ?>">Open Thread</a>
        </div>
        <?php if (strtolower($m['status'] ?? '') === 'unread'): ?>
          <form class="mt-2" method="post" action="/messages/read">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
            <button class="btn btn-sm btn-outline-primary" type="submit">Mark as Read</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
