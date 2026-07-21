<?php ob_start(); ?>
<div class="auth-header">
  <span class="material-symbols-outlined" style="font-size: 48px; color: var(--color-danger); margin-bottom: var(--space-4);">error</span>
  <h1 class="auth-title">Link Expired</h1>
  <p class="auth-subtitle" style="margin-bottom: var(--space-6);">This password reset link is invalid or has already been used.</p>
</div>

<div style="text-align: center;">
  <a href="/forgot-password" class="btn btn-primary" style="width: 100%; margin-bottom: var(--space-4);">Request New Link</a>
  <a href="/login" class="btn btn-ghost" style="width: 100%;">Return to Login</a>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/auth.php';
