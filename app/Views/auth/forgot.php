<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card">
      <div class="card-header">Forgot Password</div>
      <div class="card-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($reset_link)): ?>
          <div class="alert alert-info">Reset Link (dev): <a href="<?= htmlspecialchars($reset_link) ?>"><?= htmlspecialchars($reset_link) ?></a></div>
        <?php endif; ?>
        <form method="post" action="/password/send_reset">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">Send Reset Link</button>
        </form>
        <div class="mt-3 text-center">
          <a href="/">Back to Login</a>
        </div>
      </div>
    </div>
  </div>
</div>
