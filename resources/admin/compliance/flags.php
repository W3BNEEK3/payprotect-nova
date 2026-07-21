<?php ob_start();
/** @var array $openFlags */
$flashError   = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>
<div class="admin-compliance-flags">

    <?php if ($flashError): ?>
        <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success" data-toast="success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <div style="display: flex; flex-wrap: wrap; gap: var(--space-4); margin-bottom: var(--space-6);">
        <button type="button" class="btn btn-primary" onclick="document.getElementById('manualFlagModal').hidden = false; document.getElementById('manualUserId').focus();">
            <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px; vertical-align: -3px;">gavel</span>
            Manual Flag User
        </button>
        <a href="/admin/compliance/requirements" class="btn btn-secondary">
            <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px; vertical-align: -3px;">list_alt</span>
            Manage Requirement Types
        </a>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Flag ID</th>
                    <th>User</th>
                    <th>Reason</th>
                    <th>Date Raised</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($openFlags)): ?>
                    <tr>
                        <td colspan="5">
                            <div style="display:flex; flex-direction:column; align-items:center; padding: var(--space-12) var(--space-6); gap: var(--space-3); color: var(--slate-500);">
                                <span class="material-symbols-outlined" style="font-size:36px; opacity:.4;">check_circle</span>
                                <span style="font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body);">No open compliance flags.</span>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($openFlags as $flag): ?>
                        <tr>
                            <td data-label="Flag ID" class="td-data">#<?= htmlspecialchars($flag['id']) ?></td>
                            <td data-label="User">
                                User #<?= htmlspecialchars($flag['user_id']) ?><br>
                                <span style="font-size: 12px; color: var(--slate-500);"><?= htmlspecialchars($flag['fullname']) ?></span>
                            </td>
                            <td data-label="Reason">
                                <?php if ($flag['reason'] === 'manual'): ?>
                                    <span class="status-pill warning"><span class="material-symbols-outlined" style="font-size:14px;">front_hand</span> Manual</span>
                                <?php else: ?>
                                    <span class="status-pill warning"><span class="material-symbols-outlined" style="font-size:14px;">smart_toy</span> Auto (≥ $<?= number_format($flag['triggered_amount'], 2) ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Date Raised" class="td-data"><?= htmlspecialchars($flag['created_at']) ?></td>
                            <td data-label="Actions" style="text-align: right;">
                                <a href="/admin/compliance/<?= (int)$flag['id'] ?>/assign-code" class="btn btn-primary btn-sm">Assign Clearance Code</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Manual Flag Modal -->
<div id="manualFlagModal" class="modal-overlay" hidden>
    <div class="modal-dialog" role="dialog" aria-modal="true" style="max-width: 440px; margin: 10vh auto; padding: var(--space-6);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
            <div style="display: flex; align-items: center; gap: var(--space-3);">
                <div class="modal-icon-badge tier-caution">
                    <span class="material-symbols-outlined">gavel</span>
                </div>
                <h3 style="margin: 0; font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); color: var(--color-ink);">Flag User for Compliance</h3>
            </div>
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('manualFlagModal').hidden = true;" style="padding: var(--space-1);">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="/admin/compliance/manual-flag" method="POST">
            <?= \App\Middlewares\CsrfMiddleware::field() ?>
            <p style="font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body); color: var(--slate-700); margin-bottom: var(--space-5);">
                This will instantly pause the user's withdrawal access and notify them to contact Support.
            </p>
            
            <div class="field-group">
                <label for="manualUserId" class="label">User ID</label>
                <input type="number" id="manualUserId" name="user_id" class="input td-data" required min="1">
            </div>

            <div class="field-group">
                <label for="manualReason" class="label">Reason / Internal Notes</label>
                <input type="text" id="manualReason" name="reason" class="input" required placeholder="e.g. Suspicious login patterns">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-6);">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('manualFlagModal').hidden = true;">Cancel</button>
                <button type="submit" class="btn btn-caution">Flag Account</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$currentPath = '/admin/compliance';
require __DIR__ . '/../../layouts/admin.php';
?>
