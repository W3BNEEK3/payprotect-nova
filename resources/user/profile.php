<?php ob_start(); 
/** @var object $user */
$flashSuccess = \App\Core\Session::getFlash('success');
$flashError = \App\Core\Session::getFlash('error');

// Generate initials for avatar
$initials = strtoupper(substr($user->firstname ?? 'U', 0, 1) . substr($user->lastname ?? 'U', 0, 1));
$joinedDate = date('F Y', strtotime($user->created_at));
$accountTier = $user->is_upgraded ? 'Premium' : 'Standard';
?>

<div class="dashboard-grid">
    <div class="dashboard-sidebar">
        <!-- Profile Overview Card -->
        <div class="dashboard-section" style="text-align: center; padding: var(--space-8) var(--space-6); margin-top: 0;">
            <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--color-teal); color: white; display: inline-flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 700; margin-bottom: var(--space-4); box-shadow: 0 4px 12px rgba(13,148,136,0.3);">
                <?= $initials ?>
            </div>
            <h2 style="margin: 0; font-size: var(--type-heading-lg-size); font-weight: 600; color: var(--color-ink);">
                <?= htmlspecialchars($user->firstname . ' ' . $user->lastname) ?>
            </h2>
            <p style="color: var(--slate-500); margin: var(--space-1) 0 var(--space-4);">
                <?= htmlspecialchars($user->email ?? '') ?>
            </p>
            
            <div style="display: flex; gap: var(--space-2); justify-content: center;">
                <span style="background: <?= $user->is_upgraded ? 'var(--color-teal-100)' : 'var(--slate-100)' ?>; color: <?= $user->is_upgraded ? 'var(--color-teal)' : 'var(--slate-600)' ?>; padding: 4px 12px; border-radius: var(--radius-pill); font-size: var(--type-caption-size); font-weight: 600;">
                    <?= $accountTier ?> Tier
                </span>
                <span style="background: var(--slate-100); color: var(--slate-600); padding: 4px 12px; border-radius: var(--radius-pill); font-size: var(--type-caption-size); font-weight: 500;">
                    Member since <?= $joinedDate ?>
                </span>
            </div>
        </div>

        <!-- Personal Details -->
        <div class="dashboard-section">
            <h3 style="font-size: var(--type-heading-sm-size); font-weight: 600; margin-bottom: var(--space-4); border-bottom: 1px solid var(--slate-100); padding-bottom: var(--space-3);">Personal Details</h3>
            
            <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                <div>
                    <div style="font-size: var(--type-caption-size); color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Phone Number</div>
                    <div style="font-weight: 500; color: var(--color-ink);"><?= htmlspecialchars($user->phone ?? 'Not provided') ?></div>
                </div>
                <div>
                    <div style="font-size: var(--type-caption-size); color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Country</div>
                    <div style="font-weight: 500; color: var(--color-ink);"><?= htmlspecialchars($user->country ?? 'Not provided') ?></div>
                </div>
                <div>
                    <div style="font-size: var(--type-caption-size); color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Account Number</div>
                    <div style="font-family: var(--font-data); font-weight: 500; color: var(--color-ink);"><?= htmlspecialchars($user->account_number ?? 'Not Assigned') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="desktop-analytics" style="display: flex;">
        <!-- Security & Password -->
        <div class="dashboard-section" style="margin-top: 0;">
            <h3 style="font-size: var(--type-heading-sm-size); font-weight: 600; margin-bottom: var(--space-6); display: flex; align-items: center; gap: var(--space-2);">
                <span class="material-symbols-outlined" style="color: var(--color-teal);">lock</span> Security Settings
            </h3>
            
            <?php if ($flashSuccess): ?>
                <div class="toast toast-success" style="margin-bottom: var(--space-4);"><?= htmlspecialchars($flashSuccess) ?></div>
            <?php endif; ?>
            <?php if ($flashError): ?>
                <div class="toast toast-error" style="margin-bottom: var(--space-4);"><?= htmlspecialchars($flashError) ?></div>
            <?php endif; ?>

            <form action="/profile/password" method="POST">
                <?= \App\Middlewares\CsrfMiddleware::field() ?>
                
                <div class="field-group">
                    <label for="current_password" class="label">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="input" required>
                </div>

                <div class="field-group">
                    <label for="new_password" class="label">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="input" required>
                </div>

                <div class="field-group">
                    <label for="confirm_password" class="label">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="input" required>
                </div>

                <div style="margin-top: var(--space-6);">
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
