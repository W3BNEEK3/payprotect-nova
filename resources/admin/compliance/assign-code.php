<?php ob_start();
/** @var array $flag */
/** @var array $user */
/** @var array $requirements */
$flashError   = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>
<div class="admin-assign-code">
    <?php if ($flashError): ?>
        <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success" data-toast="success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <div style="margin-bottom: var(--space-4);">
        <a href="/admin/compliance" class="btn btn-ghost" style="display: inline-flex; align-items: center; margin-left: -8px;">
            <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px;">arrow_back</span>
            Back to Queue
        </a>
    </div>

    <div style="background: var(--color-surface); border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--slate-100); max-width: 600px;">
        <div style="padding: var(--space-5) var(--space-6); border-bottom: 1px solid var(--slate-100);">
            <h3 style="margin: 0; font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); color: var(--color-ink);">Assign Clearance Code</h3>
            <p style="margin: var(--space-1) 0 0; font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body); color: var(--slate-500);">
                Flag #<?= htmlspecialchars($flag['id']) ?> — User #<?= htmlspecialchars($user['id']) ?> (<?= htmlspecialchars($user['fullname']) ?>)
            </p>
        </div>
        <div style="padding: var(--space-6);">
            <form action="/admin/compliance/<?= (int)$flag['id'] ?>/assign-code" method="POST">
                <?= \App\Middlewares\CsrfMiddleware::field() ?>

                <div class="field-group">
                    <label for="complianceId" class="label">Requirement Type</label>
                    <select id="complianceId" name="compliance_id" class="input" required>
                        <option value="">Select requirement...</option>
                        <?php foreach ($requirements as $req): ?>
                            <?php if ($req['is_active']): ?>
                                <option value="<?= (int)$req['id'] ?>"><?= htmlspecialchars($req['name']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <div class="help-text" style="font-size: 12px; color: var(--slate-500); margin-top: 4px;">The specific document or policy the user had to clear.</div>
                </div>

                <div class="field-group">
                    <label for="code" class="label">Clearance Code</label>
                    <input type="text" id="code" name="code" class="input td-data" required style="letter-spacing: 2px;" placeholder="e.g. A9B2C4">
                    <div class="help-text" style="font-size: 12px; color: var(--slate-500); margin-top: 4px;">Generate a random string or use an internal reference number. This is the exact code Support must securely transmit to the user.</div>
                </div>
                
                <div class="field-group">
                    <label for="notes" class="label">Internal Notes (Optional)</label>
                    <input type="text" id="notes" name="notes" class="input" placeholder="e.g. Verified via IDology Call #1234">
                </div>

                <div style="margin-top: var(--space-6);">
                    <button type="submit" class="btn btn-success" style="width: 100%;">Complete Review & Assign Code</button>
                    <p style="font-size: 12px; color: var(--slate-500); margin-top: var(--space-3); text-align: center;">
                        This resolves the flag and notifies the user to contact Support for their code.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$currentPath = '/admin/compliance';
require __DIR__ . '/../../layouts/admin.php';
?>
