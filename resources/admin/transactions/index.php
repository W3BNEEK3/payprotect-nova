<?php ob_start();
/**
 * @var array $usersList
 * @var array $transactions
 * @var array $filters
 */
$flashError   = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>

<div class="admin-transactions-index">
    <?php if ($flashError): ?>
        <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success" data-toast="success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-6); flex-wrap: wrap; gap: var(--space-4);">
        <div>
            <h1 style="margin: 0 0 var(--space-2); font: 600 1.5rem/1.2 var(--font-display); color: var(--color-ink);">Transactions</h1>
            <p style="margin: 0; font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body); color: var(--slate-700);">View and filter all user transactions, or manually create a new one.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('createTransactionModal').hidden = false;">
            <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px; vertical-align: -3px;">add_circle</span>
            Create Transaction
        </button>
    </div>

    <!-- Filters Section -->
    <div style="background: var(--color-surface); padding: var(--space-4); border-radius: var(--radius-md); border: 1px solid var(--slate-200); margin-bottom: var(--space-6);">
        <form method="GET" action="/admin/transactions" style="display: flex; gap: var(--space-4); flex-wrap: wrap; align-items: flex-end;">
            
            <div class="field-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                <label for="filter_type" class="label">Type</label>
                <div class="select-wrap">
                    <select name="type" id="filter_type" class="input">
                        <option value="">All Types</option>
                        <option value="Deposit" <?= ($filters['type'] ?? '') === 'Deposit' ? 'selected' : '' ?>>Deposit</option>
                        <option value="Withdrawal" <?= ($filters['type'] ?? '') === 'Withdrawal' ? 'selected' : '' ?>>Withdrawal</option>
                        <option value="Transfer" <?= ($filters['type'] ?? '') === 'Transfer' ? 'selected' : '' ?>>Transfer</option>
                    </select>
                </div>
            </div>

            <div class="field-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                <label for="filter_user" class="label">User (Email / Account / ID)</label>
                <input type="text" name="user" id="filter_user" class="input" placeholder="e.g. john@example.com" value="<?= htmlspecialchars($filters['user'] ?? '') ?>">
            </div>

            <div class="field-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                <label for="filter_status" class="label">Status</label>
                <div class="select-wrap">
                    <select name="status" id="filter_status" class="input">
                        <option value="">All Statuses</option>
                        <option value="Completed" <?= ($filters['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Pending" <?= ($filters['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Failed" <?= ($filters['status'] ?? '') === 'Failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>
            </div>

            <div class="field-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                <label for="filter_time" class="label">Timeframe</label>
                <div class="select-wrap">
                    <select name="time" id="filter_time" class="input">
                        <option value="">All Time</option>
                        <option value="today" <?= ($filters['time'] ?? '') === 'today' ? 'selected' : '' ?>>Today</option>
                        <option value="week" <?= ($filters['time'] ?? '') === 'week' ? 'selected' : '' ?>>Last 7 Days</option>
                        <option value="month" <?= ($filters['time'] ?? '') === 'month' ? 'selected' : '' ?>>Last 30 Days</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-secondary" style="height: 40px; padding: 0 var(--space-4);">
                <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px; vertical-align: -3px;">filter_alt</span>
                Filter
            </button>
            <?php if (!empty($filters['type']) || !empty($filters['user']) || !empty($filters['status']) || !empty($filters['time'])): ?>
                <a href="/admin/transactions" class="btn btn-ghost" style="height: 40px; padding: 0 var(--space-4);">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Data Table -->
    <div style="background: var(--color-surface); border: 1px solid var(--slate-200); border-radius: var(--radius-md); overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Method</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: var(--space-6); color: var(--slate-500);">No transactions found matching your criteria.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $txn): ?>
                        <tr>
                            <td data-label="Date">
                                <?= date('M j, Y g:i A', strtotime($txn['created_at'])) ?>
                            </td>
                            <td data-label="User">
                                <?php if ($txn['user_id']): ?>
                                    <div style="display: flex; flex-direction: column;">
                                        <a href="/admin/users/<?= (int)$txn['user_id'] ?>" style="color: var(--color-teal); text-decoration: none; font-weight: 500;">
                                            <?= htmlspecialchars($txn['fullname'] ?? 'Unknown User') ?>
                                        </a>
                                        <span style="font-size: 12px; color: var(--slate-500);"><?= htmlspecialchars($txn['email'] ?? '') ?></span>
                                    </div>
                                <?php else: ?>
                                    <span style="color: var(--slate-500);">System</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Type">
                                <?= htmlspecialchars($txn['type'] ?? '') ?>
                            </td>
                            <td data-label="Amount">
                                <span style="font-weight: 600; color: var(--color-ink);">
                                    <?= htmlspecialchars($txn['currency'] ?? 'USD') ?> <?= number_format((float)$txn['amount'], 2) ?>
                                </span>
                            </td>
                            <td data-label="Status">
                                <?php
                                $statusClass = match(strtolower($txn['status'] ?? '')) {
                                    'completed', 'approved' => 'status-pill approved',
                                    'pending' => 'status-pill pending',
                                    'failed', 'rejected' => 'status-pill rejected',
                                    default => 'status-pill inactive',
                                };
                                ?>
                                <span class="<?= $statusClass ?>"><?= htmlspecialchars(ucfirst($txn['status'] ?? 'unknown')) ?></span>
                            </td>
                            <td data-label="Method">
                                <?= htmlspecialchars($txn['method'] ?? 'N/A') ?>
                            </td>
                            <td data-label="Actions" style="text-align: right;">
                                <a href="/admin/transactions/<?= $txn['id'] ?>/edit" class="btn btn-secondary" style="padding: 4px 12px; font-size: 12px; border-radius: var(--radius-sm);">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Transaction Modal -->
<div id="createTransactionModal" class="modal-overlay" hidden>
    <div class="modal-dialog" role="dialog" aria-modal="true" style="max-width: 500px; padding: var(--space-6);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
            <div style="display: flex; align-items: center; gap: var(--space-3);">
                <div class="modal-icon-badge" style="background: var(--color-teal-100); color: var(--color-teal);">
                    <span class="material-symbols-outlined">receipt_long</span>
                </div>
                <h3 style="margin: 0; font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); color: var(--color-ink);">Create Transaction</h3>
            </div>
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('createTransactionModal').hidden = true;" style="padding: var(--space-1);">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="/admin/transactions" method="POST">
            <?= \App\Middlewares\CsrfMiddleware::field() ?>
            <input type="hidden" name="source" value="index">
            
            <div class="field-group">
                <label for="create_user_id" class="label">Select User</label>
                <div class="select-wrap">
                    <select name="user_id" id="create_user_id" class="input" required>
                        <option value="">-- Choose User --</option>
                        <?php foreach ($usersList as $u): ?>
                            <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['fullname'] ?? 'User') ?> (<?= htmlspecialchars($u['email'] ?? 'Unknown Email') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: var(--space-4);">
                <div class="field-group" style="flex: 1;">
                    <label for="create_type" class="label">Transaction Type</label>
                    <div class="select-wrap">
                        <select name="type" id="create_type" class="input" required>
                            <option value="Deposit">Deposit (Credit)</option>
                            <option value="Withdrawal">Withdrawal (Debit)</option>
                            <option value="Transfer">Transfer</option>
                        </select>
                    </div>
                </div>

                <div class="field-group" style="flex: 1;">
                    <label for="create_amount" class="label">Amount</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 12px; top: 12px; color: var(--slate-400);">$</span>
                        <input type="number" step="0.01" min="0.01" name="amount" id="create_amount" class="input" style="padding-left: 28px;" required>
                    </div>
                </div>
            </div>

            <div class="field-group">
                <label for="create_method" class="label">Method</label>
                <input type="text" name="method" id="create_method" class="input" placeholder="e.g. Wire Transfer, Admin Adjustment" required>
            </div>

            <div class="field-group">
                <label for="create_party" class="label" id="create_party_label">Sender / Recipient</label>
                <input type="text" name="party" id="create_party" class="input" placeholder="e.g. John Doe, Chase Bank">
            </div>

            <div class="field-group">
                <label for="create_message" class="label">Message / Details</label>
                <input type="text" name="message" id="create_message" class="input" placeholder="e.g. Manual funding by admin">
            </div>
            
            <div style="display: flex; gap: var(--space-4);">
                <div class="field-group" style="flex: 1;">
                    <label for="create_status" class="label">Status</label>
                    <div class="select-wrap">
                        <select name="status" id="create_status" class="input" required>
                            <option value="Completed">Completed</option>
                            <option value="Pending">Pending</option>
                            <option value="Failed">Failed</option>
                        </select>
                    </div>
                </div>
                <div class="field-group" style="flex: 1;">
                    <label for="create_date" class="label">Date (Optional)</label>
                    <input type="datetime-local" name="created_at" id="create_date" class="input">
                </div>
            </div>

            <div class="field-group" style="margin-top: var(--space-4); margin-bottom: var(--space-6); background: var(--slate-50); padding: var(--space-3); border-radius: var(--radius-sm);">
                <label style="display: flex; align-items: flex-start; gap: var(--space-3); cursor: pointer; margin: 0;">
                    <input type="checkbox" name="update_balance" value="1" checked style="margin-top: 4px;">
                    <div>
                        <div style="font-weight: 500; color: var(--color-ink);">Update User Balance</div>
                        <div style="font-size: 13px; color: var(--slate-500);">Automatically credit or debit the user's account balance based on the amount and type.</div>
                    </div>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: var(--space-3);">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('createTransactionModal').hidden = true;">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Transaction</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const typeSelect = document.getElementById('create_type');
        const partyLabel = document.getElementById('create_party_label');
        if (typeSelect && partyLabel) {
            typeSelect.addEventListener('change', function() {
                if (this.value === 'Deposit') {
                    partyLabel.textContent = 'Sender';
                } else if (this.value === 'Withdrawal' || this.value === 'Transfer') {
                    partyLabel.textContent = 'Recipient';
                } else {
                    partyLabel.textContent = 'Sender / Recipient';
                }
            });
            typeSelect.dispatchEvent(new Event('change'));
        }
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
