<?php
$root = $root ?? [];
$thread = $thread ?? [];
$isCustomerThread = !empty($root['recipient_customer_id']);
?>
<div class="row">
  <div class="col-md-10">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <strong><?= htmlspecialchars($root['subject'] ?? 'Conversation') ?></strong>
          <?php if ($isCustomerThread): ?>
            <span class="badge bg-info ms-2">Customer</span>
          <?php else: ?>
            <span class="badge bg-secondary ms-2">Internal</span>
          <?php endif; ?>
        </div>
        <a href="/messages" class="btn btn-sm btn-outline-secondary">Back</a>
      </div>
      <div class="card-body">
        <?php if (empty($thread)): ?>
          <div class="alert alert-info">No messages in this thread.</div>
        <?php else: ?>
          <div class="list-group mb-3">
            <?php foreach ($thread as $m): ?>
              <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <strong><?= htmlspecialchars($m['sender_name'] ?? 'User #' . (int)($m['sender_id'] ?? 0)) ?></strong>
                    <?php if (!empty($m['recipient_customer_id'])): ?>
                      <small class="text-muted">→ <?= htmlspecialchars($m['recipient_customer_name'] ?? 'Customer #' . (int)$m['recipient_customer_id']) ?></small>
                    <?php elseif (!empty($m['recipient_name'])): ?>
                      <small class="text-muted">→ <?= htmlspecialchars($m['recipient_name']) ?></small>
                    <?php else: ?>
                      <small class="text-muted">→ Broadcast</small>
                    <?php endif; ?>
                  </div>
                  <small class="text-muted"><?= htmlspecialchars($m['created_at'] ?? '') ?></small>
                </div>
                <div class="mt-2"><?= nl2br(htmlspecialchars($m['body'] ?? '')) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" action="/messages/view">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= (int)($root['id'] ?? 0) ?>">
          <div class="mb-3">
            <label class="form-label">Reply<?= $isCustomerThread ? ' to Customer' : '' ?></label>
            <textarea class="form-control" name="body" rows="4" placeholder="Type your reply" required></textarea>
            <?php if ($isCustomerThread): ?>
              <small class="text-muted">Your reply will be emailed to the customer.</small>
            <?php endif; ?>
          </div>
          <div class="d-flex justify-content-end">
            <button class="btn btn-primary" type="submit">Send Reply</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

