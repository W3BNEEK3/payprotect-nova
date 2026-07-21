<?php ob_start(); 
$flashError = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>
<div class="admin-withdrawals">

    <?php if ($flashError): ?>
        <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success" data-toast="success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: var(--space-4); margin-bottom: var(--space-6);">
        <p style="margin: 0; font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body); color: var(--slate-700);">
            Review and process user withdrawal requests. Real funds must be transferred manually off-system before marking a request as completed.
        </p>

        <form method="GET" action="/admin/withdrawals" style="display: flex; gap: var(--space-2);">
            <select name="status" class="input" style="width: auto; padding-right: var(--space-8);" onchange="this.form.submit()">
                <option value="" <?= $statusFilter === null ? 'selected' : '' ?>>All Statuses</option>
                <option value="pending_review" <?= $statusFilter === 'pending_review' ? 'selected' : '' ?>>Pending Review</option>
                <option value="processing" <?= $statusFilter === 'processing' ? 'selected' : '' ?>>Processing</option>
                <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </form>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Req ID</th>
                    <th>User ID</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <span class="material-symbols-outlined empty-state-icon">sync_alt</span>
                                <div class="empty-state-text">No withdrawal requests found.</div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td data-label="Req ID" class="td-data">#<?= htmlspecialchars($req['id']) ?></td>
                            <td data-label="User ID" class="td-data">User #<?= htmlspecialchars($req['user_id']) ?></td>
                            <td data-label="Method" style="text-transform: capitalize;"><?= htmlspecialchars(str_replace('_', ' ', $req['method'])) ?></td>
                            <td data-label="Amount" class="td-data"><?= htmlspecialchars($req['currency']) ?> <?= number_format($req['amount'], 2) ?></td>
                            <td data-label="Status">
                                <?php
                                $statusClass = match ($req['status']) {
                                    'completed' => 'success',
                                    'rejected' => 'caution',
                                    'processing' => 'info',
                                    default => 'warning'
                                };
                                ?>
                                <span class="status-pill status-<?= $statusClass ?>">
                                    <?= htmlspecialchars(str_replace('_', ' ', $req['status'])) ?>
                                </span>
                            </td>
                            <td data-label="Date" style="color: var(--slate-500); font-size: 13px;">
                                <?= htmlspecialchars((new \DateTime($req['created_at']))->format('M j, Y H:i')) ?>
                            </td>
                            <td data-label="Action" style="text-align: right;">
                                <a href="/admin/withdrawals/<?= (int)$req['id'] ?>" class="btn btn-secondary btn-sm">
                                    Review
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: var(--space-4); display: flex; justify-content: space-between; align-items: center;">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?>" class="btn btn-ghost btn-sm">Previous Page</a>
        <?php else: ?>
            <div></div>
        <?php endif; ?>
        
        <?php if (count($requests) === 20): ?>
            <a href="?page=<?= $page + 1 ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?>" class="btn btn-ghost btn-sm">Next Page</a>
        <?php endif; ?>
    </div>

</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
