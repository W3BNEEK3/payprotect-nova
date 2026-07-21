<?php ob_start(); ?>
<div class="auth-header">
  <h1 class="auth-title">Set New Password</h1>
  <p class="auth-subtitle">Choose a new password for your account.</p>
</div>

<form action="/reset-password/<?= htmlspecialchars($token) ?>" method="POST" id="resetForm" novalidate>
  <?= \App\Middlewares\CsrfMiddleware::field() ?>
  
  <div class="field-group" style="margin-bottom: var(--space-6);">
    <label for="password" class="field-label">New Password</label>
    <div style="position: relative;">
      <input type="password" name="password" id="password" class="input <?= isset($errors['password']) ? 'has-error' : '' ?>" required style="padding-right: 40px;">
      <button type="button" class="btn btn-ghost" onclick="const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password';" style="position: absolute; right: 0; top: 0; bottom: 0; padding: 0 var(--space-3); color: var(--slate-500); height: auto; display: flex; align-items: center;">
        <span class="material-symbols-outlined" style="font-size: 20px;">visibility</span>
      </button>
    </div>
    
    <div class="password-strength-meter" style="margin-top: var(--space-2); display: flex; gap: 4px; height: 4px;">
      <div class="password-strength-fill" style="flex: 1; background: var(--slate-200); border-radius: 2px; transition: background 0.3s ease;"></div>
      <div class="password-strength-fill" style="flex: 1; background: var(--slate-200); border-radius: 2px; transition: background 0.3s ease;"></div>
      <div class="password-strength-fill" style="flex: 1; background: var(--slate-200); border-radius: 2px; transition: background 0.3s ease;"></div>
      <div class="password-strength-fill" style="flex: 1; background: var(--slate-200); border-radius: 2px; transition: background 0.3s ease;"></div>
    </div>
    <div id="password-feedback" style="font-size: 0.8rem; color: var(--slate-500); margin-top: var(--space-1); min-height: 1.2rem;"></div>

    <?php if (isset($errors['password'])): ?>
      <div class="field-error"><span class="material-symbols-outlined" aria-hidden="true">error</span><span class="field-error-text"><?= htmlspecialchars($errors['password']) ?></span></div>
    <?php endif; ?>
  </div>

  <button type="submit" class="btn btn-primary" style="width: 100%;">Save New Password</button>
</form>

<?php $extraScripts = <<<HTML
<script src="/assets/js/modules/validation.js"></script>
<script src="/assets/js/modules/field-handlers.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('resetForm');
    const pwdInput = document.getElementById('password');
    const fills = document.querySelectorAll('.password-strength-fill');
    const feedback = document.getElementById('password-feedback');

    if (pwdInput && window.NovaFieldHandlers && window.NovaFieldHandlers.Formatters.passwordStrength) {
        pwdInput.addEventListener('input', (e) => {
            const result = window.NovaFieldHandlers.Formatters.passwordStrength(e.target.value);
            fills.forEach(f => f.style.background = 'var(--slate-200)');
            if (result.score > 0) {
                let color = 'var(--color-danger)';
                if (result.score === 4) color = 'var(--color-success)';
                else if (result.score >= 2) color = 'var(--color-warning)';
                for (let i = 0; i < result.score; i++) {
                    if (fills[i]) fills[i].style.background = color;
                }
            }
            feedback.textContent = result.feedback;
        });
    }

    if (form && window.NovaValidation) {
      new window.NovaValidation.FormValidator(form, {
        password: (val) => val.length >= 8 ? null : 'Password must be at least 8 characters'
      });
    }
  });
</script>
HTML;
?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/auth.php';
