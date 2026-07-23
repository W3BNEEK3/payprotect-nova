<?php ob_start();
/** @var array $users */
$flashError   = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>

<div class="admin-users">
    <?php if ($flashError): ?>
        <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success" data-toast="success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: var(--space-4); margin-bottom: var(--space-6);">
        <div>
            <h1 style="margin: 0 0 var(--space-1); font: 600 1.5rem/1.2 var(--font-display); color: var(--color-ink);">Users Management</h1>
            <p style="margin: 0; font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body); color: var(--slate-700);">
                Manage user accounts, balances, and compliance flags.
            </p>
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Balance</th>
                    <th>Verification</th>
                    <th>Joined</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--slate-500); padding: var(--space-6);">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td data-label="ID" style="font-family: var(--font-mono); color: var(--slate-500);">
                                <a href="/admin/users/<?= (int)$u['id'] ?>" style="color: inherit; text-decoration: none;">#<?= htmlspecialchars($u['id'] ?? '') ?></a>
                            </td>
                            <td data-label="Name">
                                <a href="/admin/users/<?= (int)$u['id'] ?>" style="color: var(--color-ink); text-decoration: none;"><strong><?= htmlspecialchars($u['fullname'] ?? '') ?></strong></a>
                            </td>
                            <td data-label="Email"><?= htmlspecialchars($u['email'] ?? '') ?></td>
                            <td data-label="Balance" style="font-family: var(--font-data);">
                                <?= htmlspecialchars($u['currency'] ?? 'USD') ?> <?= number_format((float)($u['balance'] ?? 0), 2) ?>
                            </td>
                            <td data-label="Verification">
                                <?php if (($u['verification_level'] ?? 0) >= 3): ?>
                                    <span class="status-pill active">Fully Verified</span>
                                <?php elseif (($u['verification_level'] ?? 0) >= 1): ?>
                                    <span class="status-pill pending">Level <?= htmlspecialchars($u['verification_level']) ?></span>
                                <?php else: ?>
                                    <span class="status-pill inactive">Unverified</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Joined">
                                <?= date('M j, Y', strtotime($u['created_at'])) ?>
                            </td>
                            <td data-label="Actions" style="text-align: right;">
                                <div style="display: flex; gap: var(--space-2); justify-content: flex-end;">
                                    <a href="/admin/users/<?= (int)$u['id'] ?>" class="btn btn-secondary btn-sm" title="Manage User">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                                    </a>
                                    <button type="button" class="btn btn-secondary btn-sm" title="Flag for Compliance" onclick="openFlagModal(<?= (int)$u['id'] ?>)">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">gavel</span>
                                    </button>
                                </div>
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
    <div class="modal-dialog" role="dialog" aria-modal="true" style="max-width: 440px; padding: var(--space-6);">
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
                This will instantly pause the user's withdrawal access and notify them to contact Support. Are you sure you want to proceed?
            </p>
            
            <input type="hidden" id="manualUserId" name="user_id" value="">

            <div class="field-group">
                <label for="manualReason" class="label">Reason / Internal Notes</label>
                <input type="text" id="manualReason" name="reason" class="input" required placeholder="e.g. Suspicious login patterns">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-6);">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('manualFlagModal').hidden = true;">Cancel</button>
                <button type="submit" class="btn btn-caution">Yes, Flag Account</button>
            </div>
        </form>
    </div>
</div>

<script>
function openFlagModal(userId) {
    document.getElementById('manualUserId').value = userId;
    document.getElementById('manualFlagModal').hidden = false;
    document.getElementById('manualReason').focus();
}
</script>

<?php
$content = ob_get_clean();
$currentPath = '/admin/users';
require __DIR__ . '/../layouts/admin.php';
?>
