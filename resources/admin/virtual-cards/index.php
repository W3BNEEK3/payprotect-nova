<?php ob_start();
/** @var array $pendingRequests */
$flashError   = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>
<div class="admin-virtual-cards">

    <?php if ($flashError): ?>
        <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success" data-toast="success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>User</th>
                    <th>Date Requested</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pendingRequests)): ?>
                    <tr>
                        <td colspan="4">
                            <div style="display:flex; flex-direction:column; align-items:center; padding: var(--space-12) var(--space-6); gap: var(--space-3); color: var(--slate-500);">
                                <span class="material-symbols-outlined" style="font-size:36px; opacity:.4;">credit_card_off</span>
                                <span style="font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body);">No pending virtual card requests.</span>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pendingRequests as $req): ?>
                        <tr>
                            <td data-label="Request ID" class="td-data">#<?= htmlspecialchars($req['id']) ?></td>
                            <td data-label="User">User #<?= htmlspecialchars($req['user_id']) ?></td>
                            <td data-label="Date Requested" class="td-data"><?= htmlspecialchars($req['created_at']) ?></td>
                            <td data-label="Actions">
                                <div style="display: flex; gap: var(--space-2); justify-content: flex-end; flex-wrap: wrap;">
                                    <form action="/admin/virtual-cards/<?= (int)$req['id'] ?>/approve" method="POST" style="margin: 0;">
                                        <?= \App\Middlewares\CsrfMiddleware::field() ?>
                                        <!-- Success tier: positive admin completion (Design System Section 2) -->
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <!-- Caution tier: reversible rejection (Design System Section 2) -->
                                    <button type="button" class="btn btn-caution btn-sm" onclick="promptReject(<?= (int)$req['id'] ?>)">Reject</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php foreach ($pendingRequests as $req): ?>
<form id="rejectForm-<?= (int)$req['id'] ?>" action="/admin/virtual-cards/<?= (int)$req['id'] ?>/reject" method="POST" style="display:none;">
    <?= \App\Middlewares\CsrfMiddleware::field() ?>
    <input type="text" name="reason" id="rejectReason-<?= (int)$req['id'] ?>">
</form>
<?php endforeach; ?>

<?php
$content = ob_get_clean();
$currentPath = '/admin/virtual-cards';
$extraScripts = <<<JS
<script>
function promptReject(requestId) {
    NovaModal.confirm({
        tier: 'destructive',
        icon: 'cancel',
        title: 'Reject Virtual Card Request',
        body: '<p style="margin-top:0;margin-bottom:var(--space-4)">Provide a reason for rejecting request #' + requestId + ':</p><div class="field-group" style="margin-bottom:0"><input type="text" id="rejectInput" class="input" placeholder="e.g. KYC documentation incomplete" autofocus></div>',
        confirmLabel: 'Reject Request',
        cancelLabel: 'Cancel',
    }).then(function(confirmed) {
        if (!confirmed) return;
        var reason = document.getElementById('rejectInput').value.trim();
        if (!reason) {
            document.getElementById('rejectInput').focus();
            return;
        }
        document.getElementById('rejectReason-' + requestId).value = reason;
        document.getElementById('rejectForm-' + requestId).submit();
    });
}
</script>
JS;
require __DIR__ . '/../../layouts/admin.php';
?>
