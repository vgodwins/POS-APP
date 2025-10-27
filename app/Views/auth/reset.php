<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card">
      <div class="card-header">Reset Password</div>
      <div class="card-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($email)): ?>
          <div class="mb-2">Resetting password for <strong><?= htmlspecialchars($email) ?></strong></div>
        <?php endif; ?>
        <form method="post" action="/password/do_reset">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" class="form-control" name="confirm" required>
          </div>
          <button class="btn btn-success w-100" type="submit">Reset Password</button>
        </form>
        <div class="mt-3 text-center">
          <a href="/">Back to Login</a>
        </div>
      </div>
    </div>
  </div>
</div>
