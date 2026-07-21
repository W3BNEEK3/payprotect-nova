<?php ob_start(); 
/** @var array $request */
/** @var array|null $user */
$flashError = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
$details = json_decode((string)($request['destination_details'] ?? ''), true) ?: [];
?>
<div class="admin-withdrawals-show" style="max-width: 800px;">

    <?php if ($flashError): ?>
        <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success" data-toast="success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <div style="margin-bottom: var(--space-4);">
        <a href="/admin/withdrawals" class="btn btn-ghost" style="display: inline-flex; align-items: center; margin-left: -8px;">
            <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px;">arrow_back</span>
            Back to Queue
        </a>
    </div>

    <div style="background: var(--color-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--slate-100); overflow: hidden;">
        
        <div style="padding: var(--space-5) var(--space-6); border-bottom: 1px solid var(--slate-100); display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0; font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); color: var(--color-ink);">
                    Request #<?= htmlspecialchars($request['id']) ?>
                </h3>
                <p style="margin: var(--space-1) 0 0; color: var(--slate-500); font-size: 14px;">
                    Requested on <?= htmlspecialchars((new \DateTime($request['created_at']))->format('M j, Y H:i')) ?>
                </p>
            </div>
            <div>
                <?php
                $statusClass = match ($request['status']) {
                    'completed' => 'success',
                    'rejected' => 'caution',
                    'processing' => 'info',
                    default => 'warning'
                };
                ?>
                <span class="status-pill status-<?= $statusClass ?>" style="font-size: 14px; padding: 4px 12px;">
                    <?= htmlspecialchars(str_replace('_', ' ', $request['status'])) ?>
                </span>
            </div>
        </div>

        <div style="padding: var(--space-6);">
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-6);">
                <div>
                    <div style="font-size: 13px; color: var(--slate-500); margin-bottom: var(--space-1); text-transform: uppercase; letter-spacing: 0.5px;">Amount</div>
                    <div style="font: 600 var(--type-heading-lg-size)/var(--type-heading-lg-lh) var(--font-display); color: var(--color-ink);">
                        <?= htmlspecialchars($request['currency'] ?? 'USD') ?> <?= number_format($request['amount'] ?? 0, 2) ?>
                    </div>
                </div>
                <div>
                    <div style="font-size: 13px; color: var(--slate-500); margin-bottom: var(--space-1); text-transform: uppercase; letter-spacing: 0.5px;">User</div>
                    <div style="font: 500 var(--type-body-lg-size)/var(--type-body-lg-lh) var(--font-body); color: var(--color-ink);">
                        <?= htmlspecialchars($user['fullname'] ?? 'Unknown User') ?> (User #<?= htmlspecialchars($user['id'] ?? $request['user_id']) ?>)
                    </div>
                    <div style="font-size: 14px; color: var(--slate-500);">
                        Balance: <?= htmlspecialchars($user['currency'] ?? 'USD') ?> <?= number_format($user['balance'] ?? 0, 2) ?>
                    </div>
                </div>
            </div>

            <h4 style="margin: 0 0 var(--space-4); font: 600 16px/1.4 var(--font-display); color: var(--color-ink); border-top: 1px solid var(--slate-100); padding-top: var(--space-6);">
                Destination Details (<?= htmlspecialchars(ucwords(str_replace('_', ' ', $request['method']))) ?>)
            </h4>

            <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: var(--space-5); margin-bottom: var(--space-6);">
                <div style="display: grid; gap: var(--space-4);">
                    <?php if (empty($details)): ?>
                        <div style="color: var(--slate-500); font-style: italic;">No destination details provided.</div>
                    <?php else: ?>
                        <?php foreach ($details as $k => $v): ?>
                            <div>
                                <div style="font-size: 13px; color: var(--slate-500); margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <?= htmlspecialchars(str_replace('_', ' ', $k)) ?>
                                </div>
                                <div style="font: 500 15px/1.4 var(--font-mono); color: var(--color-ink); word-break: break-all; background: #fff; border: 1px solid var(--slate-200); padding: var(--space-2) var(--space-3); border-radius: var(--radius-sm);">
                                    <?= htmlspecialchars($v) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($request['status'] === 'rejected' && !empty($request['rejection_reason'])): ?>
                <div style="background: var(--color-caution-50); border-left: 4px solid var(--color-caution); padding: var(--space-4); margin-bottom: var(--space-6);">
                    <div style="font-weight: 600; color: var(--color-caution); margin-bottom: var(--space-1);">Rejection Reason</div>
                    <div style="color: var(--color-ink);"><?= htmlspecialchars($request['rejection_reason']) ?></div>
                </div>
            <?php endif; ?>

            <?php if ($request['status'] === 'pending_review'): ?>
                <div style="border-top: 1px solid var(--slate-100); padding-top: var(--space-6); display: flex; gap: var(--space-3); justify-content: flex-end; flex-wrap: wrap;">
                    
                    <button type="button" class="btn btn-caution" onclick="document.getElementById('rejectModal').hidden = false; document.getElementById('rejectionReason').focus();">
                        Reject & Refund
                    </button>
                    
                    <form action="/admin/withdrawals/<?= (int)$request['id'] ?>/approve" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you have transferred the funds? This will mark the withdrawal as completed permanently.');">
                        <?= \App\Middlewares\CsrfMiddleware::field() ?>
                        <button type="submit" class="btn btn-success">
                            Mark as Completed
                        </button>
                    </form>

                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal" hidden>
    <div class="modal-backdrop" onclick="document.getElementById('rejectModal').hidden = true;"></div>
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title">Reject Withdrawal Request</h3>
            <button type="button" class="modal-close" aria-label="Close" onclick="document.getElementById('rejectModal').hidden = true;">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="/admin/withdrawals/<?= (int)$request['id'] ?>/reject" method="POST">
            <?= \App\Middlewares\CsrfMiddleware::field() ?>
            <div class="modal-body">
                <p style="margin: 0 0 var(--space-4) 0; color: var(--slate-600); font-size: 14px;">
                    Rejecting this request will immediately refund <strong><?= htmlspecialchars($request['currency']) ?> <?= number_format($request['amount'], 2) ?></strong> back to the user's available balance.
                </p>
                <div class="field-group">
                    <label for="rejectionReason" class="label">Reason for Rejection</label>
                    <textarea id="rejectionReason" name="rejection_reason" class="input" rows="3" required placeholder="e.g. Invalid bank account details provided..."></textarea>
                    <div class="field-help">This reason will be visible to the user.</div>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: var(--space-3);">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('rejectModal').hidden = true;">Cancel</button>
                <button type="submit" class="btn btn-caution">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
