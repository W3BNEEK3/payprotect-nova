
<?php ob_start();
/** @var array|null $approvedCard */
/** @var array|null $decryptedCard */
/** @var array|null $pendingRequest */
$flashError   = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>
<div class="virtual-card-container">
    <?php if ($flashError): ?>
        <div class="toast toast-error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <?php if ($approvedCard && $decryptedCard): ?>
        <div class="virtual-card-display" style="display: flex; flex-direction: column; gap: var(--space-6); max-width: 440px;">
            <!-- card mockup -->
            <div class="card-mockup" style="background: linear-gradient(135deg, #0e7c7b 0%, #064e3b 100%); border-radius: var(--radius-md); padding: var(--space-6); color: white; box-shadow: 0 10px 30px rgba(14, 124, 123, 0.25);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-8);">
                    <span style="font-size: 0.85rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.1em; font-family: var(--font-data);">NovaTrust</span>
                    <span class="material-symbols-outlined" style="opacity: 0.8; font-size: 28px;">contactless</span>
                </div>
                <div style="font-size: 1.5rem; font-weight: 600; font-family: var(--font-mono); margin-bottom: var(--space-6); letter-spacing: 3px;">
                    **** **** **** <?= htmlspecialchars(substr($decryptedCard['number'], -4)) ?>
                </div>
                <div style="display: flex; gap: var(--space-6);">
                    <div>
                        <div style="font-size: 0.7rem; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px;">Expires</div>
                        <div style="font-family: var(--font-mono); font-size: 1rem;"><?= htmlspecialchars($decryptedCard['expiry']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.7rem; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px;">CVV</div>
                        <div style="font-family: var(--font-mono); font-size: 1rem;">•••</div>
                    </div>
                </div>
            </div>

            <div style="background: var(--color-surface); padding: var(--space-6); border-radius: var(--radius-md); border: 1px solid var(--slate-200);">
                <div style="font-size: 0.85rem; color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: var(--space-2);">Card Balance</div>
                <div style="font-size: 2.25rem; font-weight: 700; color: var(--color-teal); font-family: var(--font-data);">
                    $<?= number_format((float) $decryptedCard['balance'], 2) ?>
                </div>
                <p style="color: var(--slate-500); font-size: 0.9rem; margin: var(--space-3) 0 0;">Active &amp; ready to use for online purchases.</p>
            </div>
        </div>

    <?php elseif ($pendingRequest): ?>
        <div style="background: var(--color-surface); padding: var(--space-8); border-radius: var(--radius-md); border: 1px solid var(--slate-200); text-align: center; max-width: 480px;">
            <div style="width: 64px; height: 64px; background: var(--color-warning-100, #fef9c3); color: var(--color-warning, #d97706); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
                <span class="material-symbols-outlined" style="font-size: 32px;">pending_actions</span>
            </div>
            <h3 style="margin-top: 0; font-size: 1.15rem;">Request Under Review</h3>
            <p style="color: var(--slate-600); margin-bottom: 0;">Our compliance team is reviewing your virtual card request. You will be notified by email once a decision is made.</p>
        </div>

    <?php else: ?>
        <div style="background: var(--color-surface); padding: var(--space-8); border-radius: var(--radius-md); border: 1px solid var(--slate-200); max-width: 480px;">
            <div style="width: 64px; height: 64px; background: var(--color-teal-100, #ccfbf1); color: var(--color-teal); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-5);">
                <span class="material-symbols-outlined" style="font-size: 32px;">credit_card</span>
            </div>
            <h3 style="margin-top: 0; font-size: 1.15rem;">Get Your NovaTrust Card</h3>
            <p style="color: var(--slate-600); margin-bottom: var(--space-6);">A NovaTrust virtual card lets you make secure online payments anywhere major credit cards are accepted. It draws from your card balance.</p>
            
            <form action="/virtual-card/request" method="POST">
                <?= \App\Middlewares\CsrfMiddleware::field() ?>
                <button type="submit" class="btn btn-primary">Request Virtual Card</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$currentPath = '/virtual-card';
require __DIR__ . '/../../layouts/app.php';
?>
