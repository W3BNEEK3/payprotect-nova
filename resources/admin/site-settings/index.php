<?php ob_start();
$flashError = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>

<div class="admin-header">
    <div class="admin-header-main">
        <h1 class="admin-title">Site Settings</h1>
        <p class="admin-subtitle">Manage global website configuration.</p>
    </div>
</div>

<?php if ($flashError): ?>
    <div class="alert alert-error" style="margin-bottom: var(--space-6);"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>
<?php if ($flashSuccess): ?>
    <div class="alert alert-success" style="margin-bottom: var(--space-6);"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 800px;">
    <form action="/admin/site-settings/update" method="POST">
        <?= \App\Middlewares\CsrfMiddleware::field() ?>

        <div class="field-group">
            <label class="field-label" for="site_name">Site Name</label>
            <input type="text" class="input" id="site_name" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" required>
            <div class="field-help">The main brand name displayed across the site.</div>
        </div>

        <div class="field-group">
            <label class="field-label" for="site_logo_url">Logo URL (Optional)</label>
            <input type="text" class="input" id="site_logo_url" name="site_logo_url" value="<?= htmlspecialchars($settings['site_logo_url'] ?? '') ?>">
            <div class="field-help">Absolute URL or relative path to the logo image.</div>
        </div>

        <div class="field-group">
            <label class="field-label" for="site_favicon_url">Favicon URL</label>
            <input type="text" class="input" id="site_favicon_url" name="site_favicon_url" value="<?= htmlspecialchars($settings['site_favicon_url'] ?? '') ?>" required>
            <div class="field-help">Absolute URL or relative path to the favicon icon.</div>
        </div>

        <div class="field-group">
            <label class="field-label" for="site_support_email">Support Email</label>
            <input type="email" class="input" id="site_support_email" name="site_support_email" value="<?= htmlspecialchars($settings['site_support_email'] ?? '') ?>" required>
        </div>

        <div class="field-group">
            <label class="field-label" for="site_address">Physical Address</label>
            <textarea class="input" id="site_address" name="site_address" rows="3" required><?= htmlspecialchars($settings['site_address'] ?? '') ?></textarea>
            <div class="field-help">Used in email footers and public pages for compliance.</div>
        </div>

        <div style="margin-top: var(--space-6);">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$currentPath = '/admin/site-settings';
require __DIR__ . '/../../layouts/admin.php';
