<?php ob_start(); 
/** @var object $user */
$flashSuccess = \App\Core\Session::getFlash('success');
?>

<div class="dashboard-grid">
    <div class="dashboard-sidebar">
        <div class="dashboard-section" style="margin-top: 0;">
            <h3 style="font-size: var(--type-heading-sm-size); font-weight: 600; margin-bottom: var(--space-4); display: flex; align-items: center; gap: var(--space-2);">
                <span class="material-symbols-outlined" style="color: var(--slate-500);">tune</span> Preferences
            </h3>
            <p style="color: var(--slate-500); font-size: var(--type-body-sm-size); line-height: 1.5;">
                Manage your account preferences, notification settings, and visual layout choices. Changes are saved immediately upon form submission.
            </p>
        </div>
    </div>

    <div class="desktop-analytics" style="display: flex;">
        <div class="dashboard-section" style="margin-top: 0;">
            
            <?php if ($flashSuccess): ?>
                <div class="toast toast-success" style="margin-bottom: var(--space-4);"><?= htmlspecialchars($flashSuccess) ?></div>
            <?php endif; ?>

            <form action="/settings" method="POST">
                <?= \App\Middlewares\CsrfMiddleware::field() ?>
                
                <!-- Setting: Email Notifications -->
                <div style="display: flex; align-items: flex-start; justify-content: space-between; padding-bottom: var(--space-4); border-bottom: 1px solid var(--slate-100); margin-bottom: var(--space-4);">
                    <div>
                        <div style="font-weight: 600; color: var(--color-ink); margin-bottom: 4px;">Email Notifications</div>
                        <div style="font-size: var(--type-caption-size); color: var(--slate-500);">Receive email updates regarding your account activity and new features.</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="settings_email_notifications" <?= $user->settings_email_notifications ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <!-- Setting: Dark Mode -->
                <div style="display: flex; align-items: flex-start; justify-content: space-between; padding-bottom: var(--space-4); border-bottom: 1px solid var(--slate-100); margin-bottom: var(--space-4);">
                    <div>
                        <div style="font-weight: 600; color: var(--color-ink); margin-bottom: 4px;">Dark Mode</div>
                        <div style="font-size: var(--type-caption-size); color: var(--slate-500);">Switch the dashboard interface to a darker theme (Coming Soon).</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="settings_dark_mode" <?= $user->settings_dark_mode ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <!-- Setting: 2FA -->
                <div style="display: flex; align-items: flex-start; justify-content: space-between; padding-bottom: var(--space-4); margin-bottom: var(--space-6);">
                    <div>
                        <div style="font-weight: 600; color: var(--color-ink); margin-bottom: 4px;">Two-Factor Authentication (2FA)</div>
                        <div style="font-size: var(--type-caption-size); color: var(--slate-500);">Require an extra security code during login to protect your account.</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="settings_2fa" <?= $user->settings_2fa ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">Save Preferences</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Simple CSS Toggle Switch */
.toggle-switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}
.toggle-switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}
.toggle-switch .slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: var(--slate-200);
  transition: .2s;
  border-radius: 24px;
}
.toggle-switch .slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .2s;
  border-radius: 50%;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.toggle-switch input:checked + .slider {
  background-color: var(--color-teal);
}
.toggle-switch input:checked + .slider:before {
  transform: translateX(20px);
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
