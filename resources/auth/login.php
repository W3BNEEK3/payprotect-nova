<?php ob_start(); ?>
<div class="auth-header">
  <h1 class="auth-title">Welcome Back</h1>
  <p class="auth-subtitle">Log in to your NovaTrust account</p>
</div>

<?php if (!empty($errors['general'])): ?>
  <div class="generic-error">
    <span class="material-symbols-outlined">error</span>
    <span><?= htmlspecialchars($errors['general']) ?></span>
  </div>
<?php endif; ?>

<form action="/login" method="POST" id="loginForm" novalidate>
  <?= \App\Middlewares\CsrfMiddleware::field() ?>
  
  <div class="field-group">
    <label for="email" class="field-label">Email Address</label>
    <input type="email" name="email" id="email" class="input <?= isset($errors['email']) ? 'has-error' : '' ?>" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
    <?php if (isset($errors['email'])): ?>
      <div class="field-error"><span class="material-symbols-outlined" aria-hidden="true">error</span><span class="field-error-text"><?= htmlspecialchars($errors['email']) ?></span></div>
    <?php endif; ?>
  </div>

  <div class="field-group" style="margin-bottom: var(--space-6);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2);">
      <label for="password" class="field-label" style="margin-bottom: 0;">Password</label>
      <a href="/forgot-password" style="font-size: 0.875rem; color: var(--color-teal); text-decoration: none;">Forgot password?</a>
    </div>
    <div style="position: relative;">
      <input type="password" name="password" id="password" class="input" required style="padding-right: 40px;">
      <button type="button" class="btn btn-ghost" onclick="const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password';" style="position: absolute; right: 0; top: 0; bottom: 0; padding: 0 var(--space-3); color: var(--slate-500); height: auto; display: flex; align-items: center;">
        <span class="material-symbols-outlined" style="font-size: 20px;">visibility</span>
      </button>
    </div>
  </div>

  <button type="submit" class="btn btn-primary" style="width: 100%;">Log In</button>
</form>

<div class="auth-footer">
  Don't have an account? <a href="/register">Sign up</a>
</div>

<?php $extraScripts = <<<HTML
<script src="/assets/js/modules/validation.js"></script>
<script src="/assets/js/modules/field-handlers.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    if (form && window.NovaValidation) {
      new window.NovaValidation.FormValidator(form, {
        email: window.NovaFieldHandlers.Validators.email,
        password: (val) => val ? null : 'Enter your password'
      });
    }
  });
</script>
HTML;
?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/auth.php';
