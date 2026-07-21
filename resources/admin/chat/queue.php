<?php ob_start(); 
/** @var array $waiting */
/** @var array $active */
?>
<style>
    .chat-queue-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--space-6);
    }
    @media (max-width: 768px) {
        .chat-queue-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="chat-queue-page" style="padding-bottom: var(--space-8);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6);">
        <h1 style="font: 700 var(--type-heading-xl-size)/var(--type-heading-xl-lh) var(--font-display); color: var(--color-ink); margin: 0;">Support Queue</h1>
    </div>

    <div class="chat-queue-grid">
        
        <!-- Unassigned Waiting Queue -->
        <div style="background: var(--color-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--slate-100); padding: var(--space-6);">
            <h2 style="font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); margin: 0 0 var(--space-4); color: var(--color-ink); display: flex; align-items: center; gap: var(--space-2);">
                <span class="material-symbols-outlined" style="color: var(--color-danger);">hourglass_empty</span>
                Waiting for Agent (<?= count($waiting) ?>)
            </h2>
            
            <?php if (empty($waiting)): ?>
                <p style="color: var(--slate-500); font-style: italic;">No users waiting right now.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                    <?php foreach ($waiting as $c): ?>
                        <div style="padding: var(--space-4); border: 1px solid var(--slate-200); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 600; color: var(--color-ink);"><?= htmlspecialchars($c['firstname'] . ' ' . $c['lastname']) ?></div>
                                <div style="font-size: 0.85rem; color: var(--slate-500);"><?= htmlspecialchars($c['email']) ?></div>
                                <div style="font-size: 0.8rem; color: var(--slate-400); margin-top: var(--space-1);">Waiting since: <?= htmlspecialchars($c['updated_at']) ?></div>
                            </div>
                            <a href="/admin/chat/conversation/<?= $c['id'] ?>" class="btn btn-primary btn-sm">Claim Chat</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- My Active Conversations -->
        <div style="background: var(--color-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--slate-100); padding: var(--space-6);">
            <h2 style="font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); margin: 0 0 var(--space-4); color: var(--color-ink); display: flex; align-items: center; gap: var(--space-2);">
                <span class="material-symbols-outlined" style="color: var(--color-success);">chat</span>
                My Active Chats (<?= count($active) ?>)
            </h2>
            
            <?php if (empty($active)): ?>
                <p style="color: var(--slate-500); font-style: italic;">You have no active chats.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                    <?php foreach ($active as $c): ?>
                        <div style="padding: var(--space-4); border: 1px solid var(--slate-200); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 600; color: var(--color-ink);"><?= htmlspecialchars($c['firstname'] . ' ' . $c['lastname']) ?></div>
                                <div style="font-size: 0.85rem; color: var(--slate-500);"><?= htmlspecialchars($c['email']) ?></div>
                                <div style="font-size: 0.8rem; color: var(--slate-400); margin-top: var(--space-1);">Started: <?= htmlspecialchars($c['created_at']) ?></div>
                            </div>
                            <a href="/admin/chat/conversation/<?= $c['id'] ?>" class="btn btn-secondary btn-sm">Resume</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
