<?php ob_start(); 
$flashError = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>
<div class="dashboard-grid" style="grid-template-columns: 1fr;">
    <div class="dashboard-section" style="max-width: 500px; margin: 0 auto; margin-top: 0;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-6);">
            <div>
                <a href="/transfer" class="btn btn-ghost" style="display: inline-flex; align-items: center; margin-left: -8px; margin-bottom: var(--space-2);">
                    <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px;">arrow_back</span>
                    Back
                </a>
                <h2 style="margin: 0; font: 600 var(--type-heading-lg-size)/var(--type-heading-lg-lh) var(--font-display); color: var(--color-ink);">Internal Transfer</h2>
            </div>
            <div style="width: 48px; height: 48px; background: var(--color-teal-100); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-outlined" style="color: var(--color-teal);">compare_arrows</span>
            </div>
        </div>

        <?php if ($flashError): ?>
            <div class="toast toast-error" style="margin-bottom: var(--space-4);"><?= htmlspecialchars($flashError) ?></div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="toast toast-success" style="margin-bottom: var(--space-4);"><?= htmlspecialchars($flashSuccess) ?></div>
        <?php endif; ?>

        <form action="/transfer/internal" method="POST">
            <?= \App\Middlewares\CsrfMiddleware::field() ?>

            <div class="field-group" style="margin-bottom: var(--space-4);">
                <label for="account_number" class="label">Recipient Account Number</label>
                <input type="text" id="account_number" name="account_number" class="input" style="font-family: var(--font-data);" required placeholder="e.g. 100230495">
                <div style="font-size: var(--type-caption-size); color: var(--slate-500); margin-top: 4px;">Must be a valid NovaTrust account number.</div>
            </div>

            <div class="field-group" style="margin-bottom: var(--space-4);">
                <label for="amount" class="label">Amount (USD)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--slate-500); font-family: var(--font-data); font-weight: 500;">$</span>
                    <input type="number" step="0.01" id="amount" name="amount" class="input" style="padding-left: 32px; font-family: var(--font-data);" required placeholder="0.00">
                </div>
            </div>

            <div class="field-group" style="margin-bottom: var(--space-6);">
                <label for="message" class="label">Message / Note</label>
                <input type="text" id="message" name="message" class="input" placeholder="What is this for?">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Send Money</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
