<?php ob_start(); 
$flashError = \App\Core\Session::getFlash('error');
?>
<div class="withdraw-select-method" style="max-width: 600px; margin: 0 auto;">
    <?php if ($flashError): ?>
        <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <div style="background: var(--color-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--slate-100); overflow: hidden;">
        <div style="padding: var(--space-6); border-bottom: 1px solid var(--slate-100); text-align: center;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; background: var(--color-teal-100); color: var(--color-teal); border-radius: var(--radius-pill); margin-bottom: var(--space-4);">
                <span class="material-symbols-outlined" style="font-size: 24px;">account_balance</span>
            </div>
            <h2 style="margin: 0; font: 600 var(--type-heading-lg-size)/var(--type-heading-lg-lh) var(--font-display); color: var(--color-ink);">Select Withdrawal Method</h2>
            <p style="margin: var(--space-2) 0 0; color: var(--slate-500); font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body);">Choose how you would like to receive your funds.</p>
        </div>
        
        <div style="padding: var(--space-4);">
            <div style="display: grid; grid-template-columns: 1fr; gap: var(--space-3);">
                <?php foreach ($methods as $key => $name): ?>
                    <a href="/withdraw/<?= urlencode($key) ?>" style="display: flex; align-items: center; justify-content: space-between; padding: var(--space-4); border: 1px solid var(--slate-200); border-radius: var(--radius-md); text-decoration: none; color: var(--color-ink); transition: all var(--duration-fast) var(--ease-out);">
                        <span style="font-weight: 500;"><?= htmlspecialchars($name) ?></span>
                        <span class="material-symbols-outlined" style="color: var(--slate-400);">chevron_right</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
