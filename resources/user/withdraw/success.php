<?php ob_start(); 
$flashSuccess = \App\Core\Session::getFlash('success');
?>
<div class="withdraw-success" style="max-width: 600px; margin: 0 auto; text-align: center; padding-top: var(--space-8);">
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success" data-toast="success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <div style="background: var(--color-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); border: 1px solid var(--slate-100); padding: var(--space-8) var(--space-6);">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: #E6F4EA; color: var(--color-success); border-radius: var(--radius-pill); margin-bottom: var(--space-4);">
            <span class="material-symbols-outlined" style="font-size: 32px;">check_circle</span>
        </div>
        
        <h2 style="margin: 0; font: 600 var(--type-heading-lg-size)/var(--type-heading-lg-lh) var(--font-display); color: var(--color-ink);">Withdrawal Request Submitted</h2>
        <p style="margin: var(--space-3) 0 var(--space-6); color: var(--slate-600); font: 400 var(--type-body-lg-size)/var(--type-body-lg-lh) var(--font-body);">
            Your request to withdraw <strong><?= htmlspecialchars($request['currency'] ?? 'USD') ?> <?= number_format($request['amount'] ?? 0, 2) ?></strong> via <?= htmlspecialchars($methodName) ?> has been successfully submitted and is now pending review.
        </p>

        <div style="background: var(--slate-50); padding: var(--space-4); border-radius: var(--radius-md); text-align: left; margin-bottom: var(--space-6); display: inline-block; width: 100%; max-width: 320px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-2);">
                <span style="color: var(--slate-500); font-size: 14px;">Reference ID:</span>
                <span style="color: var(--color-ink); font-weight: 500; font-family: var(--font-mono); font-size: 14px;">#REQ-<?= htmlspecialchars($request['id']) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-2);">
                <span style="color: var(--slate-500); font-size: 14px;">Status:</span>
                <span style="color: var(--color-ink); font-weight: 500; font-size: 14px; text-transform: capitalize;"><?= htmlspecialchars(str_replace('_', ' ', $request['status'])) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--slate-500); font-size: 14px;">Date:</span>
                <span style="color: var(--color-ink); font-weight: 500; font-size: 14px;"><?= htmlspecialchars((new \DateTime($request['created_at']))->format('M j, Y H:i')) ?></span>
            </div>
        </div>

        <div>
            <a href="/transactions" class="btn btn-secondary" style="margin-right: var(--space-2);">View History</a>
            <a href="/dashboard" class="btn btn-primary">Return to Dashboard</a>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
