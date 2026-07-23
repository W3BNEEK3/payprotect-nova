<?php ob_start(); 
/** @var array $transaction */
/** @var array $user */
$flashError = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>
<div class="admin-page" style="padding: var(--space-6); max-width: 800px; margin: 0 auto;">
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-6);">
        <div>
            <a href="/admin/transactions" class="btn btn-ghost" style="display: inline-flex; align-items: center; margin-left: -8px; margin-bottom: var(--space-2);">
                <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px;">arrow_back</span>
                Back to Transactions
            </a>
            <h1 style="margin: 0; font: 600 var(--type-heading-lg-size)/var(--type-heading-lg-lh) var(--font-display); color: var(--color-ink);">Edit Transaction #<?= htmlspecialchars($transaction['id']) ?></h1>
            <p style="margin: var(--space-1) 0 0; color: var(--slate-500); font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body);">
                User: <strong style="color: var(--color-ink);"><?= htmlspecialchars($user['fullname']) ?> (<?= htmlspecialchars($user['email']) ?>)</strong>
            </p>
        </div>
    </div>

    <?php if ($flashError): ?>
        <div class="toast toast-error" style="margin-bottom: var(--space-4);"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success" style="margin-bottom: var(--space-4);"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <div style="background: var(--color-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--slate-100);">
        <form action="/admin/transactions/<?= $transaction['id'] ?>/edit" method="POST" style="padding: var(--space-6);">
            <?= \App\Middlewares\CsrfMiddleware::field() ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-bottom: var(--space-4);">
                <div class="field-group">
                    <label for="amount" class="label">Amount (<?= htmlspecialchars($transaction['currency']) ?>)</label>
                    <input type="number" step="0.01" id="amount" name="amount" class="input" value="<?= htmlspecialchars($transaction['amount']) ?>" style="font-family: var(--font-data);" required>
                </div>
                
                <div class="field-group">
                    <label for="created_at" class="label">Date & Time</label>
                    <input type="datetime-local" id="created_at" name="created_at" class="input" value="<?= date('Y-m-d\TH:i:s', strtotime($transaction['created_at'])) ?>" style="font-family: var(--font-data);" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-bottom: var(--space-4);">
                <div class="field-group">
                    <label for="type" class="label">Type</label>
                    <select id="type" name="type" class="input" required>
                        <option value="Deposit" <?= strtolower($transaction['type']) === 'deposit' ? 'selected' : '' ?>>Deposit</option>
                        <option value="Withdrawal" <?= strtolower($transaction['type']) === 'withdrawal' ? 'selected' : '' ?>>Withdrawal</option>
                        <option value="Transfer" <?= strtolower($transaction['type']) === 'transfer' ? 'selected' : '' ?>>Transfer</option>
                        <option value="Credit" <?= strtolower($transaction['type']) === 'credit' ? 'selected' : '' ?>>Credit (Admin)</option>
                        <option value="Debit" <?= strtolower($transaction['type']) === 'debit' ? 'selected' : '' ?>>Debit (Admin)</option>
                        <option value="Refund" <?= strtolower($transaction['type']) === 'refund' ? 'selected' : '' ?>>Refund</option>
                    </select>
                </div>
                
                <div class="field-group">
                    <label for="status" class="label">Status</label>
                    <select id="status" name="status" class="input" required>
                        <option value="Completed" <?= strtolower($transaction['status']) === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Pending" <?= strtolower($transaction['status']) === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Failed" <?= strtolower($transaction['status']) === 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>
            </div>

            <div class="field-group" style="margin-bottom: var(--space-4);">
                <label for="method" class="label">Method</label>
                <input type="text" id="method" name="method" class="input" value="<?= htmlspecialchars($transaction['method'] ?? '') ?>">
            </div>

            <div class="field-group" style="margin-bottom: var(--space-6);">
                <label for="message" class="label">Description / Message</label>
                <input type="text" id="message" name="message" class="input" value="<?= htmlspecialchars($transaction['message']) ?>" required>
            </div>

            <div style="padding: var(--space-4); background: var(--color-warning-100); border-radius: var(--radius-md); border: 1px solid rgba(183, 121, 31, 0.2); margin-bottom: var(--space-6);">
                <label style="display: flex; align-items: flex-start; gap: var(--space-3); cursor: pointer; margin: 0;">
                    <input type="checkbox" name="update_balance" value="1" style="margin-top: 4px; width: 18px; height: 18px;">
                    <div>
                        <div style="font-weight: 600; color: var(--color-warning); font-size: var(--type-body-md-size);">Recalculate User Balance</div>
                        <div style="font-size: var(--type-caption-size); color: var(--color-warning); opacity: 0.9; margin-top: 2px;">
                            Check this box if you want the system to mathematically reverse the original transaction and apply this new amount/type to the user's current balance. If unchecked, this edit is purely for record-keeping.
                        </div>
                    </div>
                </label>
            </div>

            <div style="display: flex; gap: var(--space-3); justify-content: flex-end; padding-top: var(--space-4); border-top: 1px solid var(--slate-100);">
                <a href="/admin/transactions" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-caution">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
