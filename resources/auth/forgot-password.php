<?php ob_start(); ?>
<div class="auth-header">
  <h1 class="auth-title">Reset Password</h1>
  <p class="auth-subtitle">Enter your email and we'll send you a link to reset your password.</p>
</div>

<?php if (!empty($success)): ?>
  <div class="generic-success">
    <span class="material-symbols-outlined">check_circle</span>
    <span>If an account exists for that email, we have sent a password reset link.</span>
  </div>
  <div style="text-align: center; margin-top: var(--space-8);">
    <a href="/login" class="btn btn-secondary">Return to login</a>
  </div>
<?php else: ?>

  <form action="/forgot-password" method="POST" id="resetRequestForm" novalidate>
    <?= \App\Middlewares\CsrfMiddleware::field() ?>
    
    <div class="field-group" style="margin-bottom: var(--space-6);">
      <label for="email" class="field-label">Email Address</label>
      <input type="email" name="email" id="email" class="input" required>
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%;">Send Reset Link</button>
  </form>

  <div class="auth-footer">
    Remembered your password? <a href="/login">Log in</a>
  </div>

  <?php $extraScripts = <<<HTML
  <script src="/assets/js/modules/validation.js"></script>
  <script src="/assets/js/modules/field-handlers.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.getElementById('resetRequestForm');
      if (form && window.NovaValidation) {
        new window.NovaValidation.FormValidator(form, {
          email: window.NovaFieldHandlers.Validators.email
        });
      }
    });
  </script>
HTML;
  ?>

<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/auth.php';
