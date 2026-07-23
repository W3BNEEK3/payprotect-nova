<?php 
$customCss = ['/assets/css/cards-page.css'];
ob_start();

/** @var array|null $user */
/** @var array|null $approvedCard */
/** @var array|null $decryptedCard */
/** @var array|null $pendingRequest */

$flashError   = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');

$userName = htmlspecialchars(trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')));
if ($userName === '') { $userName = 'CARDHOLDER'; }
$userEmail = htmlspecialchars($user['email'] ?? 'Not set');
?>
<div class="cards-page-container">
    <?php if ($flashError): ?>
        <div class="toast toast-error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <?php if ($approvedCard && $decryptedCard): ?>
    <!-- ============================================================
         STATE 1: ACTIVE CARD
         ============================================================ -->
    <?php
    // Map card_style slug to theme class + geo markup + brand HTML
    $style = $decryptedCard['card_style'] ?? 'visa_geo';
    $cardThemes = [
        'visa_geo' => [
            'class' => 'theme-visa-geo',
            'brand' => '<span class="logo-visa"><i>VISA</i></span>',
            'geo'   => '<div class="geo-pattern geo-visa"><div class="geo-shape shape-1"></div><div class="geo-shape shape-2"></div><div class="geo-shape shape-3"></div><div class="geo-shape shape-4"></div></div>',
        ],
        'mc_dark' => [
            'class' => 'theme-mc-dark',
            'brand' => '<span><div class="logo-mc"><div class="mc-red"></div><div class="mc-yellow"></div><div class="mc-overlap"></div></div></span>',
            'geo'   => '<div class="geo-pattern geo-mc-dark"><div class="mc-circle-1"></div><div class="mc-circle-2"></div><div class="mc-dot"></div></div>',
        ],
        'mc_light' => [
            'class' => 'theme-mc-light',
            'brand' => '<span><div class="logo-mc"><div class="mc-red"></div><div class="mc-yellow"></div><div class="mc-overlap"></div></div></span>',
            'geo'   => '<div class="geo-pattern geo-mc-light"><div class="pl-shape-1"></div><div class="pl-dots"></div></div>',
        ],
    ];
    $theme = $cardThemes[$style] ?? $cardThemes['visa_geo'];
    ?>
    <div class="card-page-title">
        <h1>Virtual Cards</h1>
        <p>Manage your <?= htmlspecialchars(\App\Core\Site::name()) ?> virtual card for online purchases</p>
    </div>

    <div class="card-layout">
        <!-- ---- LEFT: Card visual + quick details ---- -->
        <div class="card-left-col">

            <!-- The Card -->
            <div class="v-card-scene">
                <div class="v-card <?= htmlspecialchars($theme['class']) ?>" id="activeCard">
                    <?= $theme['geo'] ?>
                    <div class="v-card-top">
                        <?= $theme['brand'] ?>
                        <span class="material-symbols-outlined contactless-icon">contactless</span>
                    </div>
                    <div class="v-card-mid">
                        <div class="v-card-number" id="visualCardNumber" data-hidden="**** **** **** <?= htmlspecialchars(substr($decryptedCard['number'], -4)) ?>" data-full="<?= htmlspecialchars(trim(chunk_split($decryptedCard['number'], 4, ' '))) ?>">
                            **** **** **** <?= htmlspecialchars(substr($decryptedCard['number'], -4)) ?>
                        </div>
                    </div>
                    <div class="v-card-bottom">
                        <div class="v-card-name"><?= $userName ?></div>
                        <div class="v-card-expiry">
                            <small>Exp.</small>
                            <span><?= htmlspecialchars($decryptedCard['expiry'] ?? '') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status + action buttons -->
            <div style="display: flex; align-items: center; gap: var(--space-3);">
                <span class="card-status-badge badge-active">
                    <span class="badge-dot"></span>
                    Active
                </span>
                <span style="font-size: var(--type-caption-size); color: var(--slate-500);">Debit · Virtual</span>
            </div>

            <!-- Quick info panel -->
            <div class="card-quick-info" style="width: 100%; max-width: 340px; box-shadow: var(--shadow-sm); margin-top: var(--space-2);">
                <div class="info-row">
                    <span class="info-label">Card Balance</span>
                    <span class="info-val" style="color: var(--color-teal);">$<?= number_format((float)$decryptedCard['balance'], 2) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Card Number</span>
                    <span class="info-val" id="infoCardNumber" data-hidden="**** **** **** <?= htmlspecialchars(substr($decryptedCard['number'], -4)) ?>" data-full="<?= htmlspecialchars(trim(chunk_split($decryptedCard['number'], 4, ' '))) ?>">**** **** **** <?= htmlspecialchars(substr($decryptedCard['number'], -4)) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">CVV</span>
                    <span class="info-val" id="infoCardCvv" data-hidden="***" data-full="<?= htmlspecialchars($decryptedCard['cvv'] ?? '123') ?>">***</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Expiry</span>
                    <span class="info-val"><?= htmlspecialchars($decryptedCard['expiry'] ?? '') ?></span>
                </div>
            </div>

            <!-- Card action controls -->
            <div class="card-controls" style="width: 100%; max-width: 340px; background: var(--color-surface); border-radius: var(--radius-md); border: 1px solid var(--slate-200); box-shadow: var(--shadow-sm);">
                <button class="card-ctrl-btn" id="revealBtn" onclick="toggleCardDetails()">
                    <span class="material-symbols-outlined">visibility</span>
                    <span class="btn-text">Reveal Details</span>
                </button>
                <a href="/virtual-card/create" class="card-ctrl-btn">
                    <span class="material-symbols-outlined">add_card</span>
                    New Card
                </a>
                <button class="card-ctrl-btn" onclick="NovaModal.confirm({title: 'Block Card', body: 'Temporarily blocking this card will prevent any transactions. You can unblock it later.', confirmLabel: 'Block Card', tier: 'caution', icon: 'block'})">
                    <span class="material-symbols-outlined">block</span>
                    Block Card
                </button>
                <button class="card-ctrl-btn" onclick="NovaModal.confirm({title: 'Report Lost Card', body: 'Reporting your card as lost will permanently deactivate it. A new card request will need to be submitted.', confirmLabel: 'Report Lost', tier: 'destructive', icon: 'report'})">
                    <span class="material-symbols-outlined">report</span>
                    Report Lost
                </button>
            </div>
            
            <script>
            let isRevealed = false;
            function toggleCardDetails() {
                isRevealed = !isRevealed;
                const visualCardNumber = document.getElementById('visualCardNumber');
                const infoCardNumber = document.getElementById('infoCardNumber');
                const infoCardCvv = document.getElementById('infoCardCvv');
                const revealBtnText = document.querySelector('#revealBtn .btn-text');
                const revealBtnIcon = document.querySelector('#revealBtn .material-symbols-outlined');
                
                if (isRevealed) {
                    visualCardNumber.textContent = visualCardNumber.getAttribute('data-full');
                    infoCardNumber.textContent = infoCardNumber.getAttribute('data-full');
                    infoCardCvv.textContent = infoCardCvv.getAttribute('data-full');
                    revealBtnText.textContent = 'Hide Details';
                    revealBtnIcon.textContent = 'visibility_off';
                } else {
                    visualCardNumber.textContent = visualCardNumber.getAttribute('data-hidden');
                    infoCardNumber.textContent = infoCardNumber.getAttribute('data-hidden');
                    infoCardCvv.textContent = infoCardCvv.getAttribute('data-hidden');
                    revealBtnText.textContent = 'Reveal Details';
                    revealBtnIcon.textContent = 'visibility';
                }
            }
            </script>

        </div>

        <!-- ---- RIGHT: Panels ---- -->
        <div class="card-right-col">

            <!-- Recent Transactions -->
            <div class="card-panel">
                <div class="card-panel-header">
                    <h3><span class="material-symbols-outlined">receipt_long</span> Recent Transactions</h3>
                    <a href="/transactions" class="btn btn-ghost" style="font-size: var(--type-caption-size);">View all</a>
                </div>
                <div class="card-panel-body">
                    <div class="txn-row">
                        <div class="txn-icon" style="background: var(--color-teal-100);">
                            <span class="material-symbols-outlined">shopping_cart</span>
                        </div>
                        <div class="txn-info">
                            <div class="txn-name">Online Purchase</div>
                            <div class="txn-date">No transactions yet</div>
                        </div>
                        <div class="txn-amount">—</div>
                    </div>
                    <p style="text-align: center; color: var(--slate-500); font-size: var(--type-body-md-size); padding: var(--space-6) 0; margin: 0;">
                        Your card transactions will appear here once you make purchases.
                    </p>
                </div>
            </div>

            <!-- Card Limits & Settings -->
            <div class="card-panel">
                <div class="card-panel-header">
                    <h3><span class="material-symbols-outlined">tune</span> Card Settings</h3>
                </div>
                <div class="card-panel-body">
                    <div class="billing-row">
                        <span class="material-symbols-outlined">shield</span>
                        <div class="billing-row-content">
                            <div class="billing-row-label">Online Payments</div>
                            <div class="billing-row-val">Enabled</div>
                        </div>
                        <button class="btn btn-ghost" style="padding: 0; font-size: var(--type-caption-size);">Manage</button>
                    </div>
                    <div class="billing-row">
                        <span class="material-symbols-outlined">contactless</span>
                        <div class="billing-row-content">
                            <div class="billing-row-label">Contactless Payments</div>
                            <div class="billing-row-val">Enabled</div>
                        </div>
                        <button class="btn btn-ghost" style="padding: 0; font-size: var(--type-caption-size);">Manage</button>
                    </div>
                    <div class="billing-row">
                        <span class="material-symbols-outlined">lock</span>
                        <div class="billing-row-content">
                            <div class="billing-row-label">Card PIN</div>
                            <div class="billing-row-val">****</div>
                        </div>
                        <button class="btn btn-ghost" onclick="NovaModal.confirm({title: 'Change PIN', body: 'PIN management functionality is coming soon.', confirmLabel: 'Acknowledge', tier: 'primary', icon: 'lock'})" style="padding: 0; font-size: var(--type-caption-size);">Change</button>
                    </div>
                </div>
            </div>

            <!-- Billing Address -->
            <div class="card-panel">
                <div class="card-panel-header">
                    <h3><span class="material-symbols-outlined">location_on</span> Billing Address</h3>
                    <button class="btn btn-ghost" onclick="NovaModal.confirm({title: 'Update Billing Address', body: 'Billing address management is coming soon.', confirmLabel: 'Acknowledge', tier: 'primary', icon: 'edit_location'})" style="font-size: var(--type-caption-size);">Edit</button>
                </div>
                <div class="card-panel-body">
                    <div class="billing-row">
                        <span class="material-symbols-outlined">person</span>
                        <div class="billing-row-content">
                            <div class="billing-row-label">Full Name</div>
                            <div class="billing-row-val"><?= $userName ?></div>
                        </div>
                    </div>
                    <div class="billing-row">
                        <span class="material-symbols-outlined">mail</span>
                        <div class="billing-row-content">
                            <div class="billing-row-label">Email</div>
                            <div class="billing-row-val"><?= $userEmail ?></div>
                        </div>
                    </div>
                    <div class="billing-row">
                        <span class="material-symbols-outlined">home</span>
                        <div class="billing-row-content">
                            <div class="billing-row-label">Address</div>
                            <div class="billing-row-val" style="color: var(--slate-500); font-weight: 400;">Not configured</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php elseif ($pendingRequest): ?>
    <!-- ============================================================
         STATE 2: PENDING REQUEST
         ============================================================ -->
    <div class="card-empty-state">
        <div class="card-empty-icon" style="background: var(--color-warning-100); color: var(--color-warning);">
            <span class="material-symbols-outlined">hourglass_empty</span>
        </div>
        <h2>Your Card is Being Reviewed</h2>
        <p>Our compliance team is currently reviewing your virtual card request. You will receive an email notification as soon as it's approved.</p>
        <a href="/virtual-card/create" class="btn btn-primary" style="text-decoration: none;">
            Request Another Card
        </a>
    </div>

    <?php else: ?>
    <!-- ============================================================
         STATE 3: NO CARD — REDIRECT TO CREATE
         ============================================================ -->
    <div class="card-empty-state">
        <div class="card-empty-icon">
            <span class="material-symbols-outlined">credit_card</span>
        </div>
        <h2>No Active Cards</h2>
        <p>Generate a virtual card instantly for secure, traceable online purchases worldwide.</p>
        <a href="/virtual-card/create" class="btn btn-primary" style="text-decoration: none;">
            Get Your First Card
        </a>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$sidebarBrand = \App\Core\Site::name();
$currentPath = '/virtual-card';
require __DIR__ . '/../../layouts/app.php';
?>
