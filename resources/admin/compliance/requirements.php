<?php ob_start();
/** @var array $requirements */
$flashError   = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>
<div class="admin-compliance-requirements">

    <?php if ($flashError): ?>
        <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success" data-toast="success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: var(--space-4); margin-bottom: var(--space-6);">
        <div>
            <a href="/admin/compliance" class="btn btn-ghost" style="display: inline-flex; align-items: center; margin-left: -8px; margin-bottom: var(--space-2);">
                <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px;">arrow_back</span>
                Back to Flags
            </a>
            <p style="margin: 0; font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body); color: var(--slate-700);">
                Manage the catalog of compliance requirement types that can be assigned to users.
            </p>
        </div>
        <div style="display: flex; gap: var(--space-3);">
            <form action="/admin/compliance/requirements/kyc/toggle" method="POST" style="margin: 0;">
                <?= \App\Middlewares\CsrfMiddleware::field() ?>
                <button type="submit" class="btn <?= $requireKyc ? 'btn-success' : 'btn-secondary' ?>">
                    <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px; vertical-align: -3px;"><?= $requireKyc ? 'toggle_on' : 'toggle_off' ?></span>
                    Global KYC: <?= $requireKyc ? 'ON' : 'OFF' ?>
                </button>
            </form>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('createRequirementModal').hidden = false; document.getElementById('reqName').focus();">
                <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px; vertical-align: -3px;">add</span>
                New Requirement Type
            </button>
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Requirement Type</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requirements)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--slate-500); padding: var(--space-6);">No requirement types defined.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requirements as $req): ?>
                        <tr>
                            <td data-label="Type"><strong><?= htmlspecialchars($req['name']) ?></strong></td>
                            <td data-label="Description"><?= htmlspecialchars($req['description']) ?></td>
                            <td data-label="Status">
                                <?php if ($req['is_active']): ?>
                                    <span class="status-pill active">Active</span>
                                <?php else: ?>
                                    <span class="status-pill inactive">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Actions" style="text-align: right;">
                                <form action="/admin/compliance/requirements/<?= (int)$req['id'] ?>/toggle" method="POST" style="margin: 0;">
                                    <?= \App\Middlewares\CsrfMiddleware::field() ?>
                                    <button type="submit" class="btn btn-secondary btn-sm">
                                        <?= $req['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Requirement Modal -->
<div id="createRequirementModal" class="modal-overlay" hidden>
    <div class="modal-dialog" role="dialog" aria-modal="true" style="max-width: 440px; margin: 10vh auto; padding: var(--space-6);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
            <div style="display: flex; align-items: center; gap: var(--space-3);">
                <div class="modal-icon-badge tier-primary">
                    <span class="material-symbols-outlined">rule</span>
                </div>
                <h3 style="margin: 0; font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); color: var(--color-ink);">New Requirement Type</h3>
            </div>
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('createRequirementModal').hidden = true;" style="padding: var(--space-1);">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="/admin/compliance/requirements" method="POST">
            <?= \App\Middlewares\CsrfMiddleware::field() ?>
            
            <div class="field-group">
                <label for="reqName" class="label">Name</label>
                <input type="text" id="reqName" name="name" class="input" required placeholder="e.g. KYC Identity Verification">
            </div>

            <div class="field-group">
                <label for="reqDesc" class="label">Description (Internal)</label>
                <input type="text" id="reqDesc" name="description" class="input" placeholder="e.g. Requires government ID upload">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-6);">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('createRequirementModal').hidden = true;">Cancel</button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$currentPath = '/admin/compliance/requirements';
require __DIR__ . '/../../layouts/admin.php';
?>
