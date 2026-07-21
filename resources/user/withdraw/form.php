<?php ob_start(); 
/** @var string $method */
/** @var string $methodName */
/** @var float $balance */
/** @var string $currency */
$flashError = \App\Core\Session::getFlash('error');
?>
<div class="withdraw-form" style="max-width: 600px; margin: 0 auto;">
    <?php if ($flashError): ?>
        <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <div style="margin-bottom: var(--space-4);">
        <a href="/withdraw" class="btn btn-ghost" style="display: inline-flex; align-items: center; margin-left: -8px;">
            <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px;">arrow_back</span>
            Back to Methods
        </a>
    </div>

    <div style="background: var(--color-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--slate-100);">
        <div style="padding: var(--space-5) var(--space-6); border-bottom: 1px solid var(--slate-100);">
            <h3 style="margin: 0; font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); color: var(--color-ink);">Withdraw via <?= htmlspecialchars($methodName) ?></h3>
            <p style="margin: var(--space-1) 0 0; color: var(--slate-500); font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body);">
                Available Balance: <strong style="color: var(--color-ink);"><?= htmlspecialchars($currency) ?> <?= number_format($balance, 2) ?></strong>
            </p>
        </div>
        
        <div style="padding: var(--space-6);">
            <form action="/withdraw/<?= urlencode($method) ?>/review" method="POST">
                <?= \App\Middlewares\CsrfMiddleware::field() ?>

                <div class="field-group">
                    <label for="amount" class="label">Amount (<?= htmlspecialchars($currency) ?>)</label>
                    <input type="number" step="0.01" min="100" <?= $balance >= 100 ? 'max="' . htmlspecialchars($balance) . '"' : '' ?> id="amount" name="amount" class="input" required>
                    <div class="field-help">Minimum withdrawal amount is 100.</div>
                </div>

                <?php if ($method === 'bank'): ?>
                    <div class="field-group">
                        <label for="bank_name" class="label">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name" class="input" required>
                    </div>
                    <div class="field-group">
                        <label for="account_holder" class="label">Account Holder Name</label>
                        <input type="text" id="account_holder" name="account_holder" class="input" required>
                    </div>
                    <div class="field-group">
                        <label for="account_number" class="label">Account Number / IBAN</label>
                        <input type="text" id="account_number" name="account_number" class="input" required>
                    </div>
                    <div class="field-group">
                        <label for="swift_bic" class="label">Routing / SWIFT / BIC</label>
                        <input type="text" id="swift_bic" name="swift_bic" class="input" required>
                    </div>
                    <div class="field-group">
                        <label for="bank_country" class="label">Bank Country</label>
                        <input type="text" id="bank_country" name="bank_country" class="input" required>
                    </div>
                <?php elseif ($method === 'crypto'): ?>
                    <div class="field-group">
                        <label for="network" class="label">Network / Chain</label>
                        <select id="network" name="network" class="input" required>
                            <option value="">Select Network...</option>
                            <option value="BTC">Bitcoin (BTC)</option>
                            <option value="ETH">Ethereum (ERC20)</option>
                            <option value="TRX">Tron (TRC20)</option>
                            <option value="BSC">Binance Smart Chain (BEP20)</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="coin" class="label">Coin / Token</label>
                        <input type="text" id="coin" name="coin" class="input" placeholder="e.g. USDT, BTC" required>
                    </div>
                    <div class="field-group">
                        <label for="wallet_address" class="label">Wallet Address</label>
                        <input type="text" id="wallet_address" name="wallet_address" class="input" required>
                        <div class="field-help" style="color: var(--color-caution);">Please double check your wallet address. Funds sent to the wrong address cannot be recovered.</div>
                    </div>
                <?php elseif ($method === 'paypal'): ?>
                    <div class="field-group">
                        <label for="paypal_email" class="label">PayPal Email</label>
                        <input type="email" id="paypal_email" name="paypal_email" class="input" required>
                    </div>
                <?php elseif ($method === 'wise'): ?>
                    <div class="field-group">
                        <label for="recipient_name" class="label">Recipient Name</label>
                        <input type="text" id="recipient_name" name="recipient_name" class="input" required>
                    </div>
                    <div class="field-group">
                        <label for="wise_email" class="label">Wise Account Email</label>
                        <input type="email" id="wise_email" name="wise_email" class="input" required>
                    </div>
                    <div class="field-group">
                        <label for="payout_currency" class="label">Payout Currency</label>
                        <input type="text" id="payout_currency" name="payout_currency" class="input" placeholder="e.g. USD, EUR, GBP" required>
                    </div>
                <?php elseif ($method === 'skrill'): ?>
                    <div class="field-group">
                        <label for="skrill_email" class="label">Skrill Email</label>
                        <input type="email" id="skrill_email" name="skrill_email" class="input" required>
                    </div>
                <?php elseif ($method === 'western_union'): ?>
                    <div class="field-group">
                        <label for="recipient_name" class="label">Recipient Full Name</label>
                        <input type="text" id="recipient_name" name="recipient_name" class="input" required>
                    </div>
                    <div class="field-group">
                        <label for="recipient_country" class="label">Recipient Country</label>
                        <input type="text" id="recipient_country" name="recipient_country" class="input" required>
                    </div>
                    <div class="field-group">
                        <label for="recipient_phone" class="label">Recipient Phone Number</label>
                        <input type="text" id="recipient_phone" name="recipient_phone" class="input" required>
                    </div>
                <?php elseif ($method === 'google_pay'): ?>
                    <div class="field-group">
                        <label for="gpay_contact" class="label">Linked Email or Phone</label>
                        <input type="text" id="gpay_contact" name="gpay_contact" class="input" required>
                    </div>
                <?php elseif ($method === 'payoneer'): ?>
                    <div class="field-group">
                        <label for="payoneer_contact" class="label">Payoneer Email or Account ID</label>
                        <input type="text" id="payoneer_contact" name="payoneer_contact" class="input" required>
                    </div>
                <?php endif; ?>

                <div style="margin-top: var(--space-6);">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Review Withdrawal</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
