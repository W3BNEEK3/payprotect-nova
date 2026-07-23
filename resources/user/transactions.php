<?php ob_start(); 
/** @var array $transactions */
?>

<div class="dashboard-grid" style="grid-template-columns: 1fr;">
    <div class="dashboard-section" style="margin-top: 0;">
        <div class="dashboard-section-header" style="margin-bottom: var(--space-6);">
            <h3 style="font-size: var(--type-heading-md-size); font-weight: 600; display: flex; align-items: center; gap: var(--space-2);">
                <span class="material-symbols-outlined" style="color: var(--color-teal);">receipt_long</span> Transaction History
            </h3>
        </div>

        <?php if (empty($transactions)): ?>
            <div style="text-align: center; padding: var(--space-8); color: var(--slate-500); background: var(--slate-50); border-radius: var(--radius-lg); border: 1px dashed var(--slate-200);">
                <span class="material-symbols-outlined" style="font-size: 48px; opacity: 0.5; margin-bottom: var(--space-4);">history_off</span>
                <p style="font-size: var(--type-body-lg-size);">You have no transactions yet.</p>
                <p style="font-size: var(--type-body-md-size); opacity: 0.8; margin-top: var(--space-2);">Your complete history of deposits, withdrawals, and transfers will appear here.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr>
                            <th style="padding: var(--space-4); text-align: left; font-size: var(--type-caption-size); color: var(--slate-500); text-transform: uppercase; border-bottom: 1px solid var(--slate-200);">Date</th>
                            <th style="padding: var(--space-4); text-align: left; font-size: var(--type-caption-size); color: var(--slate-500); text-transform: uppercase; border-bottom: 1px solid var(--slate-200);">Description</th>
                            <th style="padding: var(--space-4); text-align: left; font-size: var(--type-caption-size); color: var(--slate-500); text-transform: uppercase; border-bottom: 1px solid var(--slate-200);">Type</th>
                            <th style="padding: var(--space-4); text-align: center; font-size: var(--type-caption-size); color: var(--slate-500); text-transform: uppercase; border-bottom: 1px solid var(--slate-200);">Status</th>
                            <th style="padding: var(--space-4); text-align: right; font-size: var(--type-caption-size); color: var(--slate-500); text-transform: uppercase; border-bottom: 1px solid var(--slate-200);">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $txn): 
                            $isCredit = in_array(strtolower($txn['type']), ['credit', 'deposit']);
                            $sign = $isCredit ? '+' : '-';
                            $amountClass = $isCredit ? 'color: var(--color-success);' : 'color: var(--color-ink);';
                            
                            $statusColors = [
                                'completed' => ['bg' => 'var(--color-success-100)', 'text' => 'var(--color-success)'],
                                'pending' => ['bg' => 'var(--color-warning-100)', 'text' => 'var(--color-warning)'],
                                'failed' => ['bg' => 'var(--color-caution-100)', 'text' => 'var(--color-caution)'],
                            ];
                            $sColor = $statusColors[strtolower($txn['status'] ?? 'completed')] ?? ['bg' => 'var(--slate-100)', 'text' => 'var(--slate-600)'];
                            $txnJson = htmlspecialchars(json_encode([
                                'amount' => number_format($txn['amount'] ?? 0, 2),
                                'currency' => $txn['currency'] ?? 'USD',
                                'sign' => $sign,
                                'isCredit' => $isCredit,
                                'status' => $txn['status'] ?? 'completed',
                                'statusBg' => $sColor['bg'],
                                'statusColor' => $sColor['text'],
                                'date' => date('M d, Y h:i A', strtotime($txn['created_at'])),
                                'message' => $txn['message'] ?? 'Transaction',
                                'type' => $txn['type'] ?? 'unknown',
                                'method' => $txn['method'] ?? 'N/A',
                                'reference' => $txn['reference'] ?? 'N/A'
                            ]));
                        ?>
                        <tr style="transition: background-color 0.2s ease; cursor: pointer;" onmouseover="this.style.backgroundColor='var(--slate-50)'" onmouseout="this.style.backgroundColor='transparent'" onclick="openTxnModal(<?= $txnJson ?>)">
                            <td style="padding: var(--space-4); border-bottom: 1px solid var(--slate-100); font-size: var(--type-body-sm-size); color: var(--slate-600);">
                                <?= date('M d, Y h:i A', strtotime($txn['created_at'])) ?>
                            </td>
                            <td style="padding: var(--space-4); border-bottom: 1px solid var(--slate-100); font-size: var(--type-body-md-size); font-weight: 500; color: var(--color-ink);">
                                <?= htmlspecialchars($txn['message'] ?? 'Transaction') ?>
                            </td>
                            <td style="padding: var(--space-4); border-bottom: 1px solid var(--slate-100); font-size: var(--type-body-sm-size); color: var(--slate-600); text-transform: capitalize;">
                                <?= htmlspecialchars($txn['type'] ?? 'unknown') ?>
                            </td>
                            <td style="padding: var(--space-4); border-bottom: 1px solid var(--slate-100); text-align: center;">
                                <span style="display: inline-block; padding: 4px 12px; border-radius: var(--radius-pill); font-size: 12px; font-weight: 600; text-transform: uppercase; background: <?= $sColor['bg'] ?>; color: <?= $sColor['text'] ?>;">
                                    <?= htmlspecialchars($txn['status'] ?? 'completed') ?>
                                </span>
                            </td>
                            <td style="padding: var(--space-4); border-bottom: 1px solid var(--slate-100); text-align: right; font-family: var(--font-data); font-weight: 600; <?= $amountClass ?>">
                                <?= $sign ?><?= htmlspecialchars($txn['currency'] ?? 'USD') ?> <?= number_format($txn['amount'] ?? 0, 2) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="txnModal" class="modal-overlay" hidden>
    <div class="modal-dialog" style="max-width: 400px; padding: var(--space-6);">
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

<script>
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

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
