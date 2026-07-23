<?php ob_start(); 
$flashError = \App\Core\Session::getFlash('error');
?>
<div class="dashboard-grid" style="grid-template-columns: 1fr;">
    <div class="dashboard-section" style="max-width: 600px; margin: 0 auto; margin-top: 0;">
        <?php if ($flashError): ?>
            <div class="toast toast-error" style="margin-bottom: var(--space-4);"><?= htmlspecialchars($flashError) ?></div>
        <?php endif; ?>

        <div style="text-align: center; margin-bottom: var(--space-8);">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; background: var(--color-teal-100); color: var(--color-teal); border-radius: var(--radius-pill); margin-bottom: var(--space-4);">
                <span class="material-symbols-outlined" style="font-size: 28px;">send_money</span>
            </div>
            <h2 style="margin: 0; font: 600 var(--type-heading-lg-size)/var(--type-heading-lg-lh) var(--font-display); color: var(--color-ink);">Send Money / Transfer</h2>
            <p style="margin: var(--space-2) 0 0; color: var(--slate-500); font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body);">Select a transfer method to continue.</p>
        </div>

        <div style="display: flex; flex-direction: column; gap: var(--space-4);">
            
            <a href="/transfer/internal" style="display: flex; align-items: center; gap: var(--space-4); padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--slate-200); border-radius: var(--radius-md); text-decoration: none; color: inherit; transition: all var(--duration-fast) var(--ease-out); box-shadow: var(--shadow-sm);" onmouseover="this.style.borderColor='var(--color-teal)'; this.style.boxShadow='var(--shadow-md)';" onmouseout="this.style.borderColor='var(--slate-200)'; this.style.boxShadow='var(--shadow-sm)';">
                <div style="width: 48px; height: 48px; background: var(--slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="color: var(--color-teal);">compare_arrows</span>
                </div>
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 4px; font: 600 var(--type-heading-sm-size)/var(--type-heading-sm-lh) var(--font-body); color: var(--color-ink);">Internal Transfer</h3>
                    <p style="margin: 0; color: var(--slate-500); font: 400 var(--type-caption-size)/var(--type-caption-lh) var(--font-body);">Send money instantly to another NovaTrust user.</p>
                </div>
                <span class="material-symbols-outlined" style="color: var(--slate-400);">chevron_right</span>
            </a>

            <a href="/transfer/bank" style="display: flex; align-items: center; gap: var(--space-4); padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--slate-200); border-radius: var(--radius-md); text-decoration: none; color: inherit; transition: all var(--duration-fast) var(--ease-out); box-shadow: var(--shadow-sm);" onmouseover="this.style.borderColor='var(--color-teal)'; this.style.boxShadow='var(--shadow-md)';" onmouseout="this.style.borderColor='var(--slate-200)'; this.style.boxShadow='var(--shadow-sm)';">
                <div style="width: 48px; height: 48px; background: var(--slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="color: var(--color-teal);">account_balance</span>
                </div>
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 4px; font: 600 var(--type-heading-sm-size)/var(--type-heading-sm-lh) var(--font-body); color: var(--color-ink);">Local Bank Transfer</h3>
                    <p style="margin: 0; color: var(--slate-500); font: 400 var(--type-caption-size)/var(--type-caption-lh) var(--font-body);">Withdraw funds to a domestic bank account.</p>
                </div>
                <span class="material-symbols-outlined" style="color: var(--slate-400);">chevron_right</span>
            </a>

            <a href="/transfer/international" style="display: flex; align-items: center; gap: var(--space-4); padding: var(--space-6); background: var(--color-surface); border: 1px solid var(--slate-200); border-radius: var(--radius-md); text-decoration: none; color: inherit; transition: all var(--duration-fast) var(--ease-out); box-shadow: var(--shadow-sm);" onmouseover="this.style.borderColor='var(--color-teal)'; this.style.boxShadow='var(--shadow-md)';" onmouseout="this.style.borderColor='var(--slate-200)'; this.style.boxShadow='var(--shadow-sm)';">
                <div style="width: 48px; height: 48px; background: var(--slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="color: var(--color-teal);">public</span>
                </div>
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 4px; font: 600 var(--type-heading-sm-size)/var(--type-heading-sm-lh) var(--font-body); color: var(--color-ink);">International Transfer</h3>
                    <p style="margin: 0; color: var(--slate-500); font: 400 var(--type-caption-size)/var(--type-caption-lh) var(--font-body);">Send money globally using SWIFT/IBAN.</p>
                </div>
                <span class="material-symbols-outlined" style="color: var(--slate-400);">chevron_right</span>
            </a>

        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
