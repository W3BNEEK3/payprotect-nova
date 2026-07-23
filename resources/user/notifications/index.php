<?php ob_start(); 
/** @var array $grouped */
/** @var string $filter */
$flashError = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>
<div class="notifications-page" style="max-width: 800px; margin: 0 auto;">

    <?php if ($flashError): ?>
        <div class="toast toast-error" data-toast="error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
        <div class="toast toast-success" data-toast="success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <div style="margin-bottom: var(--space-6);">
        <h2 style="margin: 0 0 var(--space-3) 0; font: 600 var(--type-heading-lg-size)/var(--type-heading-lg-lh) var(--font-display); color: var(--color-ink);">Notifications</h2>
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: var(--space-3);">
            <div style="display: flex; gap: var(--space-2); background: var(--color-paper); padding: 4px; border-radius: var(--radius-md);">
                <a href="/notifications?filter=all" style="padding: 4px 12px; font-size: 14px; font-weight: 500; text-decoration: none; border-radius: var(--radius-sm); color: <?= $filter === 'all' ? 'var(--color-ink)' : 'var(--slate-500)' ?>; background: <?= $filter === 'all' ? 'var(--color-surface)' : 'transparent' ?>; box-shadow: <?= $filter === 'all' ? 'var(--shadow-sm)' : 'none' ?>;">All</a>
                <a href="/notifications?filter=unread" style="padding: 4px 12px; font-size: 14px; font-weight: 500; text-decoration: none; border-radius: var(--radius-sm); color: <?= $filter === 'unread' ? 'var(--color-ink)' : 'var(--slate-500)' ?>; background: <?= $filter === 'unread' ? 'var(--color-surface)' : 'transparent' ?>; box-shadow: <?= $filter === 'unread' ? 'var(--shadow-sm)' : 'none' ?>;">Unread</a>
            </div>
            
            <button type="button" id="pageMarkAllRead" class="btn btn-ghost" style="color: var(--color-teal); font-size: 14px; padding: 0;">Mark all read</button>
        </div>
    </div>

    <?php if (empty($grouped)): ?>
        <div style="text-align: center; padding: var(--space-12) 0; background: var(--color-surface); border-radius: var(--radius-lg); border: 1px solid var(--slate-100);">
            <span class="material-symbols-outlined" style="font-size: 48px; color: var(--slate-300); margin-bottom: var(--space-4);">notifications_off</span>
            <h3 style="margin: 0 0 var(--space-2); font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); color: var(--color-ink);">No Notifications</h3>
            <p style="margin: 0; color: var(--slate-500);">You're all caught up!</p>
        </div>
    <?php else: ?>
        <div style="background: var(--color-surface); border-radius: var(--radius-lg); border: 1px solid var(--slate-100); overflow: hidden;">
            <?php foreach ($grouped as $date => $items): ?>
                <div style="background: var(--slate-50); padding: var(--space-3) var(--space-6); font-size: 12px; font-weight: 600; color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--slate-100);">
                    <?php
                        $d = new \DateTime($date);
                        $today = new \DateTime();
                        $yesterday = (new \DateTime())->modify('-1 day');
                        if ($d->format('Y-m-d') === $today->format('Y-m-d')) echo 'Today';
                        elseif ($d->format('Y-m-d') === $yesterday->format('Y-m-d')) echo 'Yesterday';
                        else echo $d->format('F j, Y');
                    ?>
                </div>
                
                <?php foreach ($items as $item): 
                    $isRead = (int)$item['is_read'] === 1;
                ?>
                    <div class="page-notification-item <?= $isRead ? '' : 'unread' ?>" data-id="<?= $item['id'] ?>" style="padding: var(--space-4) var(--space-6); border-bottom: 1px solid var(--slate-100); background: <?= $isRead ? 'transparent' : 'rgba(10, 163, 151, 0.05)' ?>; display: flex; align-items: flex-start; gap: var(--space-4);">
                        <div style="margin-top: 2px;">
                            <span class="material-symbols-outlined" style="color: <?= $isRead ? 'var(--slate-400)' : 'var(--color-teal)' ?>; font-size: 24px; font-variation-settings: 'FILL' <?= $isRead ? '0' : '1' ?>;">
                                <?= $item['type'] === 'withdrawal_pending' ? 'sync_alt' : ($item['type'] === 'card_approved' ? 'credit_card' : 'notifications') ?>
                            </span>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: <?= $isRead ? '500' : '600' ?>; color: var(--color-ink); margin-bottom: 4px;">
                                <?= htmlspecialchars($item['title'] ?? 'Notification') ?>
                                <?php if (!$isRead): ?>
                                    <span class="page-unread-dot" style="display:inline-block; width:8px; height:8px; background:var(--color-teal); border-radius:50%; margin-left:4px;"></span>
                                <?php endif; ?>
                            </div>
                            <div style="color: var(--slate-600); line-height: 1.5; margin-bottom: 8px;">
                                <?= htmlspecialchars($item['message'] ?? '') ?>
                            </div>
                            <div style="font-size: 12px; color: var(--slate-500);">
                                <?= (new \DateTime($item['created_at']))->format('g:i A') ?>
                            </div>
                        </div>
                        <?php if (!$isRead): ?>
                            <button type="button" class="btn btn-ghost page-mark-read-btn" data-id="<?= $item['id'] ?>" style="padding: 6px; color: var(--slate-400);" title="Mark as read">
                                <span class="material-symbols-outlined" style="font-size: 18px;">check</span>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    document.querySelectorAll('.page-mark-read-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const id = btn.getAttribute('data-id');
            const itemDiv = btn.closest('.page-notification-item');
            
            try {
                const formData = new URLSearchParams();
                formData.append('_csrf', csrfToken);
                
                const res = await fetch(`/api/notifications/${id}/mark-read`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData.toString()
                });
                
                if (res.ok) {
                    itemDiv.classList.remove('unread');
                    itemDiv.style.background = 'transparent';
                    const titleDiv = itemDiv.querySelector('div[style*="font-weight"]');
                    if(titleDiv) titleDiv.style.fontWeight = '500';
                    const dot = itemDiv.querySelector('.page-unread-dot');
                    if (dot) dot.remove();
                    const icon = itemDiv.querySelector('.material-symbols-outlined');
                    if (icon) {
                        icon.style.color = 'var(--slate-400)';
                        icon.style.fontVariationSettings = "'FILL' 0";
                    }
                    btn.remove();
                }
            } catch (err) {}
        });
    });

    const markAllBtn = document.getElementById('pageMarkAllRead');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', async () => {
            try {
                const formData = new URLSearchParams();
                formData.append('_csrf', csrfToken);
                
                const res = await fetch(`/api/notifications/mark-all-read`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData.toString()
                });
                
                if (res.ok) {
                    window.location.reload();
                }
            } catch (err) {}
        });
    }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
