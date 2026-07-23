<?php 
/**
 * @var array $user
 * @var array|null $card
 * @var float $incomeTrend
 * @var array $spendData
 */
$customCss = ['/assets/css/dashboard.css', '/assets/css/cards-page.css'];
ob_start(); 
$flashError = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');

// Helpers for data formatting
$firstName = htmlspecialchars($user['firstname'] ?? 'User');
$balance = number_format($user['balance'] ?? 0, 2);
$currency = htmlspecialchars($user['currency'] ?? 'USD');

// Card theme lookup (mirrors VirtualCardController + create.php)
$cardStyle = $card['card_style'] ?? 'visa_geo';
$cardThemes = [
    'visa_geo' => [
        'class' => 'theme-visa-geo',
        'brand' => '<span class="logo-visa"><i>VISA</i></span>',
        'geo'   => '<div class="geo-pattern geo-visa"><div class="geo-shape shape-1"></div><div class="geo-shape shape-2"></div><div class="geo-shape shape-3"></div><div class="geo-shape shape-4"></div></div>',
    ],
    'mc_dark' => [
        'class' => 'theme-mc-dark',
        'brand' => '<div class="logo-mc"><div class="mc-red"></div><div class="mc-yellow"></div><div class="mc-overlap"></div></div>',
        'geo'   => '<div class="geo-pattern geo-mc-dark"><div class="mc-circle-1"></div><div class="mc-circle-2"></div><div class="mc-dot"></div></div>',
    ],
    'mc_light' => [
        'class' => 'theme-mc-light',
        'brand' => '<div class="logo-mc"><div class="mc-red"></div><div class="mc-yellow"></div><div class="mc-overlap"></div></div>',
        'geo'   => '<div class="geo-pattern geo-mc-light"><div class="pl-shape-1"></div><div class="pl-dots"></div></div>',
    ],
];
$cTheme = $cardThemes[$cardStyle] ?? $cardThemes['visa_geo'];

// Mask card number if virtual card exists
$maskedCard = '**** **** **** ****';
$expiry = '--/--';
if ($card) {
    $cardIdString = (string)$card['id'];
    $maskedCard = '**** **** **** ' . str_pad($cardIdString, 4, '0', STR_PAD_LEFT);
    if (isset($card['expiry_date'])) {
        $expiry = $card['expiry_date'];
    }
}
// Monthly Income & Trend styling
$trendColor = $incomeTrend >= 0 ? 'var(--color-success)' : 'var(--color-error)';
$trendIcon = $incomeTrend >= 0 ? 'trending_up' : 'trending_down';
$trendSign = $incomeTrend >= 0 ? '+' : '';

// Spend Breakdown Logic
$totalSpend = $spendData['total'];
$transfersVal = $spendData['breakdown']['transfers'];
$cardsVal = $spendData['breakdown']['cards'];
$othersVal = $spendData['breakdown']['others'];

$transfersPct = $totalSpend > 0 ? ($transfersVal / $totalSpend) * 100 : 0;
$cardsPct = $totalSpend > 0 ? ($cardsVal / $totalSpend) * 100 : 0;
$othersPct = $totalSpend > 0 ? ($othersVal / $totalSpend) * 100 : 0;

$c1End = $transfersPct;
$c2End = $c1End + $cardsPct;

if ($totalSpend == 0) {
    $conicGradient = "var(--slate-200) 0% 100%";
} else {
    $conicGradient = "var(--color-teal) 0% {$c1End}%, var(--color-ink) {$c1End}% {$c2End}%, var(--color-warning) {$c2End}% 100%";
}
?>

<div class="dashboard-grid">
    <!-- Main Column (Mobile + Desktop Left) -->
    <div>
        <?php if ($flashError): ?>
            <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="toast toast-success" data-toast="success"><?= htmlspecialchars($flashSuccess) ?></div>
        <?php endif; ?>

        <?php if (!empty($hasOpenFlag)): ?>
            <div style="background: var(--color-warning-100); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: var(--radius-md); padding: var(--space-4); margin-bottom: var(--space-6); display: flex; align-items: flex-start; gap: var(--space-3);">
                <span class="material-symbols-outlined" style="color: var(--color-warning); font-size: 24px;">warning</span>
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 var(--space-1); font: 600 var(--type-body-md-size)/1 var(--font-body); color: var(--color-warning);">Account Under Compliance Review</h3>
                    <p style="margin: 0 0 var(--space-3); font: 400 var(--type-caption-size)/1.5 var(--font-body); color: var(--slate-700);">Your withdrawal access and card application features are paused until the review is complete.</p>
                    <a href="/compliance" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">Complete Verification →</a>
                </div>
            </div>
        <?php endif; ?>
        
        <header style="margin-bottom: var(--space-6); display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="color: var(--slate-500); font-size: var(--type-body-md-size); margin: 0;">Welcome Back!</p>
                <h1 style="font-family: var(--font-display); font-size: var(--type-heading-lg-size); margin: 0;"><?= $firstName ?>.</h1>
            </div>
        </header>

        <!-- Wallet Balance Box -->
        <div class="balance-box">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span class="label">Total Balance</span>
                <button type="button" class="btn btn-icon" id="toggleBalanceBtn" style="width: 32px; height: 32px; min-height: 32px; color: var(--slate-500); background: transparent; padding: 0;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">visibility_off</span>
                </button>
            </div>
            <div class="balance-amount" id="balanceAmountContainer">
                <span class="currency-symbol"><?= $currency === 'USD' ? '$' : ($currency === 'EUR' ? '€' : '') ?></span><span id="balanceValue"><?= $balance ?></span>
            </div>
        </div>

        <!-- Cards Carousel -->
        <div class="cards-carousel">
            <?php if ($card): ?>
            <!-- Active Virtual Card — uses user's chosen theme -->
            <a href="/virtual-card" class="hero-card v-card <?= htmlspecialchars($cTheme['class']) ?>" style="text-decoration:none; flex-shrink: 0;">
                <?= $cTheme['geo'] ?>
                <div class="v-card-top" style="z-index:2; position:relative;">
                    <?= $cTheme['brand'] ?>
                    <span class="material-symbols-outlined contactless-icon">contactless</span>
                </div>
                <div class="v-card-mid" style="z-index:2; position:relative; flex:1; display:flex; align-items:center;">
                    <div class="v-card-number" style="font-size:13px; letter-spacing:2px;"><?= $maskedCard ?></div>
                </div>
                <div class="v-card-bottom" style="z-index:2; position:relative;">
                    <div class="v-card-name" style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:1px; opacity:.9;"><?= htmlspecialchars($firstName) ?></div>
                    <div class="v-card-expiry">
                        <small>Exp.</small>
                        <span style="font-family:var(--font-data); font-size:12px;"><?= $expiry ?></span>
                    </div>
                </div>
            </a>
            <?php endif; ?>
            
            <!-- Add Card Button / Placeholder Card -->
            <a href="/virtual-card" class="add-card-placeholder">
                <div class="add-card-icon"><span class="material-symbols-outlined">add</span></div>
                <span>Add New Card</span>
            </a>
        </div>

        <!-- Quick Actions -->
        <style>
            @media (min-width: 1024px) {
                .hide-desktop-if-zero { display: <?= $totalSpend == 0 ? 'none' : 'flex' ?> !important; }
            }
            .promo-banner {
                background: var(--slate-900);
                border-radius: var(--radius-lg);
                padding: var(--space-6);
                color: white;
                position: relative;
                overflow: hidden;
            }
            @media (max-width: 1023px) {
                .promo-banner { display: none; }
                .big-action-desc { display: none; }
                .mobile-tight { 
                    padding: 0 !important; 
                    border: none !important; 
                }
            }
            .promo-banner::before {
                content: '';
                position: absolute;
                top: -50px;
                right: -50px;
                width: 150px;
                height: 150px;
                border-radius: 50%;
                background: rgba(255,255,255,0.1);
            }
            .big-action-btn {
                background: var(--color-surface);
                border: 1px solid var(--slate-200);
                border-radius: var(--radius-lg);
                padding: var(--space-4);
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: var(--space-3);
                text-decoration: none;
                color: var(--color-ink);
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .big-action-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            }
            .big-action-icon {
                width: 48px;
                height: 48px;
                border-radius: 50%;
                background: var(--color-teal-100);
                color: var(--color-teal);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
            }
        </style>
        <!-- Small action buttons removed as requested (replaced by large grid on right) -->


    </div>

    <!-- Desktop Secondary Column (Analytics Overview) -->
    <div class="desktop-analytics">

        <div class="dashboard-section mobile-tight" style="margin-top: 0; box-shadow: none; border: 1px solid var(--slate-200); background: transparent;">
            
                <div class="promo-banner" style="margin-bottom: var(--space-6);">
                    <h3 style="margin: 0 0 var(--space-2); font-size: var(--type-heading-md-size); font-weight: 600; color: white;">Welcome to <?= htmlspecialchars(\App\Core\Site::name()) ?>!</h3>
                    <p style="margin: 0 0 var(--space-4); opacity: 0.9; font-size: var(--type-body-md-size);">Your account is fully set up. Start sending money or applying for virtual cards to unlock your financial freedom.</p>
                    <a href="/support" class="btn" style="background: white; color: var(--color-ink); font-weight: 600; text-decoration: none !important;">Explore Features</a>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                    <a href="/transfer" class="big-action-btn">
                        <div class="big-action-icon"><span class="material-symbols-outlined">payments</span></div>
                        <div style="font-weight: 600;">Send Money</div>
                        <div class="big-action-desc" style="font-size: var(--type-caption-size); color: var(--slate-500);">Transfer funds globally with ease.</div>
                    </a>
                    <a href="/virtual-card" class="big-action-btn">
                        <div class="big-action-icon" style="background: var(--slate-100); color: var(--color-ink);"><span class="material-symbols-outlined">credit_card</span></div>
                        <div style="font-weight: 600;">Virtual Cards</div>
                        <div class="big-action-desc" style="font-size: var(--type-caption-size); color: var(--slate-500);">Create cards for secure online payments.</div>
                    </a>
                    <a href="/transactions" class="big-action-btn">
                        <div class="big-action-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--color-warning);"><span class="material-symbols-outlined">history</span></div>
                        <div style="font-weight: 600;">Transaction History</div>
                        <div class="big-action-desc" style="font-size: var(--type-caption-size); color: var(--slate-500);">Track your spending and income.</div>
                    </a>
                    <a href="/support" class="big-action-btn">
                        <div class="big-action-icon" style="background: var(--color-teal-100); color: var(--color-teal);"><span class="material-symbols-outlined">support_agent</span></div>
                        <div style="font-weight: 600;">24/7 Support</div>
                        <div class="big-action-desc" style="font-size: var(--type-caption-size); color: var(--slate-500);">We are here to help anytime.</div>
                    </a>
                </div>
        </div>
    </div>
</div>

<!-- Full Width Recent Activity -->
<div class="dashboard-section">
    <div class="dashboard-section-header">
        <h3>Recent Activity</h3>
        <a href="/transactions" class="view-all">View All</a>
    </div>
    
    <?php if (empty($transactions)): ?>
        <div style="text-align: center; padding: var(--space-6); color: var(--slate-500);">
            <span class="material-symbols-outlined" style="font-size: 48px; opacity: 0.5;">receipt_long</span>
            <p>No recent activity.</p>
        </div>
    <?php else: ?>
        <div class="txn-list">
            <?php foreach ($transactions as $txn): 
                $isCredit = in_array(strtolower($txn['type']), ['credit', 'deposit']);
                $icon = $isCredit ? 'arrow_downward' : 'arrow_upward';
                $sign = $isCredit ? '+' : '-';
                $amount = number_format($txn['amount'], 2);
                $date = new DateTime($txn['created_at']);
                
                $statusColors = [
                    'completed' => ['bg' => 'var(--color-success-100)', 'text' => 'var(--color-success)'],
                    'pending' => ['bg' => 'var(--color-warning-100)', 'text' => 'var(--color-warning)'],
                    'failed' => ['bg' => 'var(--color-caution-100)', 'text' => 'var(--color-caution)'],
                ];
                $sColor = $statusColors[strtolower($txn['status'] ?? 'completed')] ?? ['bg' => 'var(--slate-100)', 'text' => 'var(--slate-600)'];

                $txnJson = htmlspecialchars(json_encode([
                    'amount' => $amount,
                    'currency' => $txn['currency'] ?? 'USD',
                    'sign' => $sign,
                    'isCredit' => $isCredit,
                    'status' => $txn['status'] ?? 'completed',
                    'statusBg' => $sColor['bg'],
                    'statusColor' => $sColor['text'],
                    'date' => $date->format('d M, h:i a'),
                    'message' => $txn['message'] ?? ucfirst($txn['type']),
                    'type' => $txn['type'] ?? 'unknown',
                    'method' => $txn['method'] ?? 'N/A',
                    'reference' => $txn['reference'] ?? 'N/A'
                ]));
            ?>
            <div class="txn-item" style="cursor: pointer;" onclick="openTxnModal(<?= $txnJson ?>)">
                <div class="txn-icon <?= $isCredit ? 'credit' : 'debit' ?>">
                    <span class="material-symbols-outlined"><?= $icon ?></span>
                </div>
                <div class="txn-details">
                    <div class="txn-title"><?= htmlspecialchars($txn['message'] ?? ucfirst($txn['type'])) ?></div>
                    <div class="txn-date"><?= $date->format('d M, h:i a') ?></div>
                </div>
                <div class="txn-amount <?= $isCredit ? 'credit' : 'debit' ?>">
                    <?= $sign ?><?= $currency === 'USD' ? '$' : '' ?><?= $amount ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('toggleBalanceBtn');
    const balanceValue = document.getElementById('balanceValue');
    if (!toggleBtn || !balanceValue) return;

    let isHidden = localStorage.getItem('nova_balance_hidden') === 'true';
    const actualBalance = balanceValue.innerText;

    const updateUI = () => {
        if (isHidden) {
            balanceValue.innerText = '****';
            toggleBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 20px;">visibility</span>';
        } else {
            balanceValue.innerText = actualBalance;
            toggleBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 20px;">visibility_off</span>';
        }
    };

    updateUI();

    toggleBtn.addEventListener('click', () => {
        isHidden = !isHidden;
        localStorage.setItem('nova_balance_hidden', isHidden.toString());
        updateUI();
    });
});

function openTxnModal(txn) {
    document.getElementById('txnModalAmount').innerText = txn.sign + txn.currency + ' ' + txn.amount;
    document.getElementById('txnModalAmount').style.color = txn.isCredit ? 'var(--color-success)' : 'var(--color-ink)';
    
    document.getElementById('txnModalStatus').innerText = txn.status;
    document.getElementById('txnModalStatus').style.background = txn.statusBg;
    document.getElementById('txnModalStatus').style.color = txn.statusColor;
    
    document.getElementById('txnModalIconBg').style.background = txn.isCredit ? 'var(--color-success-100)' : 'var(--slate-100)';
    document.getElementById('txnModalIconBg').style.color = txn.isCredit ? 'var(--color-success)' : 'var(--slate-600)';
    document.getElementById('txnModalIcon').innerText = txn.isCredit ? 'arrow_downward' : 'arrow_upward';
    
    document.getElementById('txnModalDate').innerText = txn.date;
    document.getElementById('txnModalMessage').innerText = txn.message;
    document.getElementById('txnModalType').innerText = txn.type;
    document.getElementById('txnModalMethod').innerText = txn.method;
    document.getElementById('txnModalRef').innerText = txn.reference;
    
    document.getElementById('txnModal').hidden = false;
}
</script>

<div id="txnModal" class="modal-overlay" hidden>
    <div class="modal-dialog" style="max-width: 400px; padding: var(--space-6); position: relative; z-index: 1000;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
            <div style="display: flex; align-items: center; gap: var(--space-3);">
                <div id="txnModalIconBg" class="modal-icon-badge">
                    <span id="txnModalIcon" class="material-symbols-outlined">receipt_long</span>
                </div>
                <h3 style="margin:0; font: 600 var(--type-heading-md-size)/1 var(--font-display);">Transaction Details</h3>
            </div>
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('txnModal').hidden = true;" style="padding: 4px;">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <div style="text-align: center; margin: var(--space-6) 0;">
            <div id="txnModalAmount" style="font-family: var(--font-data); font-size: 32px; font-weight: 700;">$0.00</div>
            <div id="txnModalStatus" style="display: inline-block; padding: 4px 12px; border-radius: var(--radius-pill); font-size: 12px; font-weight: 600; text-transform: uppercase; margin-top: 8px;">Completed</div>
        </div>

        <div style="background: var(--slate-50); border-radius: var(--radius-md); padding: var(--space-4); display: flex; flex-direction: column; gap: var(--space-3);">
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--slate-500); font-size: var(--type-caption-size);">Date</span>
                <span id="txnModalDate" style="font-weight: 500; font-size: var(--type-body-sm-size);"></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--slate-500); font-size: var(--type-caption-size);">Description</span>
                <span id="txnModalMessage" style="font-weight: 500; font-size: var(--type-body-sm-size);"></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--slate-500); font-size: var(--type-caption-size);">Type</span>
                <span id="txnModalType" style="font-weight: 500; font-size: var(--type-body-sm-size); text-transform: capitalize;"></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--slate-500); font-size: var(--type-caption-size);">Method</span>
                <span id="txnModalMethod" style="font-weight: 500; font-size: var(--type-body-sm-size);"></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--slate-500); font-size: var(--type-caption-size);">Reference ID</span>
                <span id="txnModalRef" style="font-weight: 500; font-family: var(--font-data); font-size: 12px;"></span>
            </div>
        </div>
    </div>
</div>


<?php 
$content = ob_get_clean();
$sidebarBrand = 'NovaTrust';
$currentPath = '/dashboard';
require __DIR__ . '/../layouts/app.php'; 
?>
