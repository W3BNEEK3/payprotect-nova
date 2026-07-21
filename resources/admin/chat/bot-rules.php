<?php ob_start(); ?>
<div class="chat-bot-rules-page" style="padding-bottom: var(--space-8);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6);">
        <h1 style="font: 700 var(--type-heading-xl-size)/var(--type-heading-xl-lh) var(--font-display); color: var(--color-ink); margin: 0;">Chat Bot Rules</h1>
        <button class="btn btn-primary">Add New Rule</button>
    </div>

    <div style="background: var(--color-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--slate-100); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--slate-50); border-bottom: 1px solid var(--slate-200); text-align: left;">
                    <th style="padding: var(--space-3) var(--space-4); font-weight: 600; color: var(--slate-600); font-size: 0.85rem; text-transform: uppercase;">Trigger Keywords</th>
                    <th style="padding: var(--space-3) var(--space-4); font-weight: 600; color: var(--slate-600); font-size: 0.85rem; text-transform: uppercase;">Bot Response</th>
                    <th style="padding: var(--space-3) var(--space-4); font-weight: 600; color: var(--slate-600); font-size: 0.85rem; text-transform: uppercase;">Action</th>
                    <th style="padding: var(--space-3) var(--space-4); font-weight: 600; color: var(--slate-600); font-size: 0.85rem; text-transform: uppercase; text-align: right;">Manage</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rules as $rule): ?>
                    <tr style="border-bottom: 1px solid var(--slate-100);">
                        <td style="padding: var(--space-3) var(--space-4); font-size: 0.9rem;">
                            <?php foreach (explode(',', $rule['trigger_keywords']) as $kw): ?>
                                <span style="background: var(--slate-100); padding: 2px 6px; border-radius: 4px; font-family: var(--font-mono); font-size: 0.75rem; margin-right: 4px; display: inline-block; margin-bottom: 4px;"><?= htmlspecialchars(trim($kw)) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td style="padding: var(--space-3) var(--space-4); font-size: 0.9rem; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?= htmlspecialchars($rule['response']) ?>
                        </td>
                        <td style="padding: var(--space-3) var(--space-4); font-size: 0.9rem;">
                            <?php if ($rule['requires_human']): ?>
                                <span style="color: var(--color-danger); font-weight: 600; display: inline-flex; align-items: center; gap: 4px;"><span class="material-symbols-outlined" style="font-size: 16px;">support_agent</span> Escalate</span>
                            <?php else: ?>
                                <span style="color: var(--color-success); font-weight: 600; display: inline-flex; align-items: center; gap: 4px;"><span class="material-symbols-outlined" style="font-size: 16px;">check_circle</span> Resolve</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: var(--space-3) var(--space-4); text-align: right;">
                            <button class="btn btn-ghost btn-sm">Edit</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rules)): ?>
                    <tr>
                        <td colspan="4" style="padding: var(--space-6); text-align: center; color: var(--slate-500); font-style: italic;">No rules defined yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
