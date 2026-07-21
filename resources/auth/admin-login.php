<?php ob_start(); ?>

<div class="auth-header">
  <h1 class="auth-title">Welcome Back</h1>
  <p class="auth-subtitle">Log in to the NovaTrust Control Center</p>
</div>

<form method="POST" action="/control-center/auth" class="auth-form" novalidate>
  <?= \App\Middlewares\CsrfMiddleware::field() ?>

  <?php if (!empty($errors['general'])): ?>
    <div class="generic-error">
      <span class="material-symbols-outlined">error</span>
      <span><?= htmlspecialchars($errors['general']) ?></span>
    </div>
  <?php endif; ?>

  <div class="field-group">
    <label for="email" class="field-label">Email Address</label>
    <input type="email" id="email" name="email" class="input <?= isset($errors['email']) ? 'has-error' : '' ?>"
           value="<?= htmlspecialchars($old['email'] ?? '') ?>"
           required autofocus>
    <?php if (isset($errors['email'])): ?>
      <div class="field-error"><?= htmlspecialchars($errors['email']) ?></div>
    <?php endif; ?>
  </div>

  <div class="field-group">
    <label for="password" class="field-label">Password</label>
    <div class="input-password-wrap">
      <input type="password" id="password" name="password" class="input <?= isset($errors['password']) ? 'has-error' : '' ?>" required>
      <button type="button" class="password-toggle" aria-label="Show password">
        <span class="material-symbols-outlined">visibility</span>
      </button>
    </div>
    <?php if (isset($errors['password'])): ?>
      <div class="field-error"><?= htmlspecialchars($errors['password']) ?></div>
    <?php endif; ?>
  </div>

  <button type="submit" class="btn btn-primary">Log In</button>
</form>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/control-center-auth.php'; 
?>
