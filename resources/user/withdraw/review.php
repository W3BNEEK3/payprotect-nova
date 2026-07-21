<?php ob_start(); 
$flashError = \App\Core\Session::getFlash('error');
?>
<div class="withdraw-review" style="max-width: 600px; margin: 0 auto;">
    <?php if ($flashError): ?>
        <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <div style="margin-bottom: var(--space-4);">
        <a href="/withdraw/<?= urlencode($method) ?>" class="btn btn-ghost" style="display: inline-flex; align-items: center; margin-left: -8px;">
            <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px;">arrow_back</span>
            Back to Edit Details
        </a>
    </div>

    <div style="background: var(--color-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--slate-100);">
        <div style="padding: var(--space-5) var(--space-6); border-bottom: 1px solid var(--slate-100); text-align: center;">
            <h3 style="margin: 0; font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); color: var(--color-ink);">Review Withdrawal</h3>
            <div style="margin-top: var(--space-4); font: 700 var(--type-heading-xl-size)/var(--type-heading-xl-lh) var(--font-display); color: var(--color-ink);">
                <?= htmlspecialchars($currency) ?> <?= number_format($amount, 2) ?>
            </div>
            <p style="margin: 0; color: var(--slate-500); font-size: 14px;">via <?= htmlspecialchars($methodName) ?></p>
        </div>
        
        <div style="padding: var(--space-6);">
            <div style="background: var(--slate-50); padding: var(--space-4); border-radius: var(--radius-md); border: 1px solid var(--slate-100); margin-bottom: var(--space-6);">
                <h4 style="margin: 0 0 var(--space-3) 0; font-size: 14px; font-weight: 600; color: var(--slate-700); text-transform: uppercase; letter-spacing: 0.5px;">Destination Details</h4>
                <div style="display: grid; gap: var(--space-2);">
                    <?php foreach ($details as $key => $val): ?>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: var(--space-4);">
                            <span style="color: var(--slate-500); font-size: 14px; text-transform: capitalize;"><?= htmlspecialchars(str_replace('_', ' ', $key)) ?>:</span>
                            <span style="color: var(--color-ink); font-weight: 500; font-size: 14px; text-align: right; word-break: break-all;">
                                <?php
                                    // Mask some details slightly for security/review aesthetic
                                    $displayVal = htmlspecialchars($val);
                                    echo $displayVal;
                                ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <form action="/withdraw/submit" method="POST">
                <?= \App\Middlewares\CsrfMiddleware::field() ?>
                
                <div style="background: var(--color-paper); border-left: 4px solid var(--color-teal); padding: var(--space-4); border-radius: 0 var(--radius-sm) var(--radius-sm) 0; margin-bottom: var(--space-6);">
                    <p style="margin: 0; font-size: 14px; color: var(--slate-700); line-height: 1.5;">
                        <strong style="color: var(--color-ink);">Important:</strong> Please confirm your details are correct. 
                        Once submitted, this amount will be immediately deducted from your available balance while the request is reviewed by our processing team.
                    </p>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Confirm & Submit Withdrawal</button>
            </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
