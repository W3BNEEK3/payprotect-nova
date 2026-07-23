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
                    <th>User / Account</th>
                    <th>Reason</th>
                    <th>Assigned Code</th>
                    <th>Date Raised</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($openFlags)): ?>
                    <tr>
                        <td colspan="6">
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
                            <td data-label="User / Account">
                                <span class="td-data"><?= htmlspecialchars($flag['account_number']) ?></span><br>
                                <span style="font-size: 12px; color: var(--slate-500);"><?= htmlspecialchars($flag['email']) ?></span>
                            </td>
                            <td data-label="Reason">
                                <?php if ($flag['reason'] === 'manual'): ?>
                                    <span class="status-pill warning"><span class="material-symbols-outlined" style="font-size:14px;">front_hand</span> Manual</span>
                                <?php else: ?>
                                    <span class="status-pill warning"><span class="material-symbols-outlined" style="font-size:14px;">smart_toy</span> Auto (≥ $<?= number_format($flag['triggered_amount'], 2) ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Assigned Code">
                                <?php if ($flag['assigned_code']): ?>
                                    <span class="td-data"><?= htmlspecialchars($flag['assigned_code']) ?></span><br>
                                    <span style="font-size: 12px; color: var(--slate-500);"><?= htmlspecialchars($flag['requirement_name']) ?></span>
                                <?php else: ?>
                                    <span style="color: var(--slate-400); font-size: 12px; font-style: italic;">None assigned</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Date Raised" class="td-data"><?= htmlspecialchars($flag['created_at']) ?></td>
                            <td data-label="Actions" style="text-align: right;">
                                <?php if ($flag['assigned_code']): ?>
                                    <button type="button" class="btn btn-secondary btn-sm" disabled>Code Assigned</button>
                                <?php else: ?>
                                    <a href="/admin/compliance/<?= (int)$flag['id'] ?>/assign-code" class="btn btn-primary btn-sm">Assign Clearance Code</a>
                                <?php endif; ?>
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
                This will instantly pause the user's withdrawal access and notify them to contact Support.
            </p>
            
            <div class="field-group">
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: var(--space-1);">
                    <label for="userSearchInput" class="label" style="margin-bottom: 0;">User (Search by Email)</label>
                    <button type="button" id="browseUsersBtn" class="btn btn-ghost btn-sm" style="font-size: 12px; padding: 0 4px; height: auto;">Browse all</button>
                </div>
                <div style="position: relative;">
                    <input type="text" id="userSearchInput" class="input" placeholder="Type email to search..." autocomplete="off">
                    <input type="hidden" id="manualUserId" name="user_id" required>
                    <div id="userSearchResults" class="autocomplete-dropdown" hidden></div>
                </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearchInput');
    const hiddenIdInput = document.getElementById('manualUserId');
    const resultsContainer = document.getElementById('userSearchResults');
    let debounceTimer;

    if (!searchInput) return;

    function renderUsers(users) {
        resultsContainer.innerHTML = '';
        if (users.length === 0) {
            resultsContainer.innerHTML = '<div class="autocomplete-item empty">No users found</div>';
        } else {
            users.forEach(u => {
                const div = document.createElement('div');
                div.className = 'autocomplete-item';
                div.innerHTML = `<strong style="display:block;margin-bottom:2px;">${u.fullname}</strong><small style="color:var(--slate-500);">${u.email} (ID: #${u.id})</small>`;
                div.onclick = function() {
                    searchInput.value = u.email;
                    hiddenIdInput.value = u.id;
                    resultsContainer.hidden = true;
                };
                resultsContainer.appendChild(div);
            });
        }
        resultsContainer.hidden = false;
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        
        hiddenIdInput.value = '';

        if (query.length < 2) {
            resultsContainer.hidden = true;
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/admin/users/search?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(renderUsers);
        }, 300);
    });

    const browseBtn = document.getElementById('browseUsersBtn');
    if (browseBtn) {
        browseBtn.addEventListener('click', function() {
            hiddenIdInput.value = '';
            searchInput.value = '';
            fetch(`/admin/users/search?all=1`)
                .then(res => res.json())
                .then(renderUsers);
        });
    }

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.hidden = true;
        }
    });
});
</script>

<?php
$content = ob_get_clean();
$currentPath = '/admin/compliance';
require __DIR__ . '/../../layouts/admin.php';
?>
