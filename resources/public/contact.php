<?php
/**
 * @var bool $success
 * @var array|null $old
 * @var array|null $errors
 */
ob_start();
?>

<section class="hero-section" style="min-height: 400px;">
  <div class="hero-inner" style="grid-template-columns: 1fr;">
    <div class="hero-copy" style="max-width: 800px; margin: 0 auto; text-align: center;">
      <h1 class="hero-load-item">Contact <?= htmlspecialchars(\App\Core\Site::name()) ?></h1>
      <p class="hero-subhead hero-load-item" style="max-width: 600px; margin: 0 auto;">
        Get in touch with our team. We're here to help.
      </p>
    </div>
  </div>
</section>

<section class="public-section">
  <div class="scroll-reveal" style="max-width: 540px; margin: 0 auto; background: var(--color-surface); padding: var(--space-8); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
    
    <?php if ($success): ?>
      <div class="toast-success" style="padding: var(--space-4); background: var(--color-success-100); color: var(--color-success); border-radius: var(--radius-md); margin-bottom: var(--space-6); display: flex; align-items: center; gap: var(--space-2);">
        <span class="material-symbols-outlined">check_circle</span>
        <span class="type-body-md" style="font-weight: 500;">Your message has been sent. We'll be in touch shortly.</span>
      </div>
    <?php endif; ?>

    <form action="/contact" method="POST" id="contactForm" novalidate>
      <div class="field-group">
        <label for="email" class="field-label">Email Address</label>
        <input type="email" name="email" id="email" class="input <?= isset($errors['email']) ? 'has-error' : '' ?>" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
        <?php if (isset($errors['email'])): ?>
          <div class="field-error">
            <span class="material-symbols-outlined" aria-hidden="true">error</span>
            <span class="field-error-text"><?= htmlspecialchars($errors['email']) ?></span>
          </div>
        <?php endif; ?>
      </div>

      <div class="field-group">
        <label for="subject" class="field-label">Subject</label>
        <input type="text" name="subject" id="subject" class="input <?= isset($errors['subject']) ? 'has-error' : '' ?>" value="<?= htmlspecialchars($old['subject'] ?? '') ?>" required>
        <?php if (isset($errors['subject'])): ?>
          <div class="field-error">
            <span class="material-symbols-outlined" aria-hidden="true">error</span>
            <span class="field-error-text"><?= htmlspecialchars($errors['subject']) ?></span>
          </div>
        <?php endif; ?>
      </div>

      <div class="field-group" style="margin-bottom: var(--space-6);">
        <label for="message" class="field-label">Message</label>
        <textarea name="message" id="message" rows="5" class="input <?= isset($errors['message']) ? 'has-error' : '' ?>" required><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
        <?php if (isset($errors['message'])): ?>
          <div class="field-error">
            <span class="material-symbols-outlined" aria-hidden="true">error</span>
            <span class="field-error-text"><?= htmlspecialchars($errors['message']) ?></span>
          </div>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
    </form>
  </div>
</section>

<?php $extraScripts = <<<HTML
<script src="/assets/js/modules/validation.js"></script>
<script src="/assets/js/modules/field-handlers.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('contactForm');
    if (form && window.NovaValidation) {
      new window.NovaValidation.FormValidator(form, {
        email: window.NovaFieldHandlers.Validators.email,
        subject: (val) => val.trim() ? null : 'Enter a subject',
        message: (val) => val.length >= 10 ? null : 'Message must be at least 10 characters'
      });
    }
  });
</script>
HTML;
?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../resources/layouts/public.php';
