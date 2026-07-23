<?php ob_start();
/** @var array $user */
$flashError   = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>

<div class="admin-user-show">
    <?php if ($flashError): ?>
        <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success" data-toast="success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: var(--space-4); margin-bottom: var(--space-6);">
        <div>
            <a href="/admin/users" class="btn btn-ghost" style="display: inline-flex; align-items: center; margin-left: -8px; margin-bottom: var(--space-2);">
                <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px;">arrow_back</span>
                Back to Users
            </a>
            <h1 style="margin: 0 0 var(--space-1); font: 600 1.5rem/1.2 var(--font-display); color: var(--color-ink);">Manage User: <?= htmlspecialchars($user['fullname'] ?? 'Unknown User') ?></h1>
            <p style="margin: 0; font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body); color: var(--slate-700);">
                Account ID: #<?= htmlspecialchars($user['id']) ?>
            </p>
        </div>
        <div style="display: flex; gap: var(--space-3);">
            <button type="button" class="btn btn-secondary" onclick="openFlagModal(<?= (int)$user['id'] ?>)">
                <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px; vertical-align: -3px;">gavel</span>
                Flag for Compliance
            </button>
            <form action="/admin/users/<?= (int)$user['id'] ?>/impersonate" method="POST" style="margin: 0;">
                <?= \App\Middlewares\CsrfMiddleware::field() ?>
                <button type="submit" class="btn btn-primary" onclick="return confirm('You will be logged in as this user and redirected. Continue?');">
                    <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px; vertical-align: -3px;">login</span>
                    Login as User
                </button>
            </form>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 320px), 1fr)); gap: var(--space-6);">
        
        <!-- Balance Management -->
        <div style="background: var(--color-surface); border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: var(--space-6);">
            <h3 style="margin: 0 0 var(--space-4); font: 600 var(--type-heading-sm-size)/1.2 var(--font-display); color: var(--color-ink);">Balance Management</h3>
            
            <div style="margin-bottom: var(--space-6); display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: var(--space-3);">
                <div>
                    <p style="margin: 0 0 var(--space-1); color: var(--slate-500); font-size: var(--type-caption-size); text-transform: uppercase; letter-spacing: 0.05em;">Current Balance</p>
                    <p style="margin: 0; font: 600 1.5rem/1 var(--font-data); color: var(--color-ink);"><?= htmlspecialchars($user['currency'] ?? 'USD') ?> <?= number_format((float)($user['balance'] ?? 0), 2) ?></p>
                </div>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('createTransactionModal').hidden = false;">
                    <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px; vertical-align: -3px;">add_circle</span>
                    Create Transaction
                </button>
            </div>

            <form action="/admin/users/<?= (int)$user['id'] ?>/balance" method="POST">
                <?= \App\Middlewares\CsrfMiddleware::field() ?>
                <div class="field-group" style="margin-bottom: var(--space-4);">
                    <label for="amount" class="label">Amount</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 12px; top: 12px; color: var(--slate-400);">$</span>
                        <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="input" style="padding-left: 28px;" required>
                    </div>
                </div>
                <div style="display: flex; gap: var(--space-3);">
                    <button type="submit" name="action" value="credit" class="btn btn-success" style="flex: 1;">Credit (+)</button>
                    <button type="submit" name="action" value="debit" class="btn btn-error" style="flex: 1;">Debit (-)</button>
                </div>
            </form>
        </div>

        <!-- Edit Profile -->
        <div style="background: var(--color-surface); border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: var(--space-6);">
            <h3 style="margin: 0 0 var(--space-4); font: 600 var(--type-heading-sm-size)/1.2 var(--font-display); color: var(--color-ink);">Profile Details</h3>
            
            <form action="/admin/users/<?= (int)$user['id'] ?>" method="POST">
                <?= \App\Middlewares\CsrfMiddleware::field() ?>
                
                <div class="field-group">
                    <label for="fullname" class="label">Full Name</label>
                    <input type="text" name="fullname" id="fullname" class="input" value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" required>
                </div>

                <div class="field-group">
                    <label for="email" class="label">Email Address</label>
                    <input type="email" name="email" id="email" class="input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                </div>

                <div class="field-group">
                    <label for="account_number" class="label">Account Number</label>
                    <input type="text" name="account_number" id="account_number" class="input" value="<?= htmlspecialchars($user['account_number'] ?? '') ?>">
                </div>

                <div class="field-group">
                    <label for="currency" class="label">Currency</label>
                    <input type="text" name="currency" id="currency" class="input" value="<?= htmlspecialchars($user['currency'] ?? 'USD') ?>" maxlength="3">
                </div>

                <div style="display: flex; gap: var(--space-4); margin-bottom: var(--space-6);">
                    <label style="display: flex; align-items: center; gap: var(--space-2); cursor: pointer;">
                        <input type="checkbox" name="is_kyc_verified" <?= !empty($user['is_kyc_verified']) ? 'checked' : '' ?>>
                        KYC Verified
                    </label>
                    <label style="display: flex; align-items: center; gap: var(--space-2); cursor: pointer;">
                        <input type="checkbox" name="is_upgraded" <?= !empty($user['is_upgraded']) ? 'checked' : '' ?>>
                        Account Upgraded
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Save Changes</button>
            </form>
        </div>

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
$content = ob_get_clean();?>
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
            <input type="hidden" name="source" value="user_profile">
            <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">

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
$currentPath = '/admin/users';
require __DIR__ . '/../../layouts/admin.php';
?>
