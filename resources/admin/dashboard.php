<?php ob_start(); 
/** @var array $stats */
?>

<div class="admin-dashboard">
    <div style="margin-bottom: var(--space-8);">
        <h1 style="margin: 0 0 var(--space-2); font: 600 1.75rem/1.2 var(--font-display); color: var(--color-ink);">Control Center</h1>
        <p style="margin: 0; font: 400 var(--type-body-md-size)/var(--type-body-md-lh) var(--font-body); color: var(--slate-700);">System overview and pending actions.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-8);">
        
        <a href="/admin/users" style="display: block; text-decoration: none; background: var(--color-surface); border-radius: var(--radius-md); border: 1px solid var(--slate-200); padding: var(--space-6); box-shadow: var(--shadow-sm); transition: transform 0.2s, box-shadow 0.2s;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
                <div style="width: 40px; height: 40px; border-radius: var(--radius-sm); background: var(--color-teal-100); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="color: var(--color-teal);">group</span>
                </div>
            </div>
            <h3 style="margin: 0 0 var(--space-1); font: 400 var(--type-caption-size)/1 var(--font-body); color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.05em;">Total Users</h3>
            <p style="margin: 0; font: 600 2rem/1 var(--font-display); color: var(--color-ink);"><?= number_format($stats['totalUsers']) ?></p>
        </a>

        <a href="/admin/withdrawals" style="display: block; text-decoration: none; background: var(--color-surface); border-radius: var(--radius-md); border: 1px solid var(--slate-200); padding: var(--space-6); box-shadow: var(--shadow-sm); transition: transform 0.2s, box-shadow 0.2s;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
                <div style="width: 40px; height: 40px; border-radius: var(--radius-sm); background: var(--slate-100); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="color: var(--slate-700);">sync_alt</span>
                </div>
                <?php if ($stats['pendingWithdrawals'] > 0): ?>
                    <span style="background: var(--color-warning); color: white; padding: 2px 8px; border-radius: var(--radius-full); font-size: 11px; font-weight: 600;"><?= $stats['pendingWithdrawals'] ?> Pending</span>
                <?php endif; ?>
            </div>
            <h3 style="margin: 0 0 var(--space-1); font: 400 var(--type-caption-size)/1 var(--font-body); color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.05em;">Withdrawals</h3>
            <p style="margin: 0; font: 600 2rem/1 var(--font-display); color: var(--color-ink);"><?= number_format($stats['pendingWithdrawals']) ?></p>
        </a>

        <a href="/admin/virtual-cards" style="display: block; text-decoration: none; background: var(--color-surface); border-radius: var(--radius-md); border: 1px solid var(--slate-200); padding: var(--space-6); box-shadow: var(--shadow-sm); transition: transform 0.2s, box-shadow 0.2s;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
                <div style="width: 40px; height: 40px; border-radius: var(--radius-sm); background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="color: #3b82f6;">credit_card</span>
                </div>
                <?php if ($stats['pendingCards'] > 0): ?>
                    <span style="background: var(--color-warning); color: white; padding: 2px 8px; border-radius: var(--radius-full); font-size: 11px; font-weight: 600;"><?= $stats['pendingCards'] ?> Pending</span>
                <?php endif; ?>
            </div>
            <h3 style="margin: 0 0 var(--space-1); font: 400 var(--type-caption-size)/1 var(--font-body); color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.05em;">Card Requests</h3>
            <p style="margin: 0; font: 600 2rem/1 var(--font-display); color: var(--color-ink);"><?= number_format($stats['pendingCards']) ?></p>
        </a>

        <a href="/admin/compliance" style="display: block; text-decoration: none; background: var(--color-surface); border-radius: var(--radius-md); border: 1px solid var(--slate-200); padding: var(--space-6); box-shadow: var(--shadow-sm); transition: transform 0.2s, box-shadow 0.2s;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
                <div style="width: 40px; height: 40px; border-radius: var(--radius-sm); background: var(--color-warning-100); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="color: var(--color-warning);">gavel</span>
                </div>
                <?php if ($stats['openFlags'] > 0): ?>
                    <span style="background: var(--color-warning); color: white; padding: 2px 8px; border-radius: var(--radius-full); font-size: 11px; font-weight: 600;"><?= $stats['openFlags'] ?> Open</span>
                <?php endif; ?>
            </div>
            <h3 style="margin: 0 0 var(--space-1); font: 400 var(--type-caption-size)/1 var(--font-body); color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.05em;">Compliance Flags</h3>
            <p style="margin: 0; font: 600 2rem/1 var(--font-display); color: var(--color-ink);"><?= number_format($stats['openFlags']) ?></p>
        </a>

    </div>
</div>

<?php 
$content = ob_get_clean();
$sidebarBrand = 'Control Center';
$currentPath = '/admin/dashboard';
require __DIR__ . '/../layouts/admin.php'; 
?>
