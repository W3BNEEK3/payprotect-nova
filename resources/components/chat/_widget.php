<?php
// Chat Widget HTML - loaded on-demand
?>
<div id="nova-chat-widget" class="chat-widget" style="display: none; position: fixed; bottom: 20px; right: 20px; width: 350px; height: 500px; max-height: calc(100vh - 80px); background: var(--color-surface, #fff); border-radius: var(--radius-lg, 12px); box-shadow: 0 8px 30px rgba(0,0,0,0.15); border: 1px solid #e2e8f0; z-index: 9999; flex-direction: column; overflow: hidden;">
<div style="background: #000000; color: #ffffff; padding: var(--space-4); display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
    <div style="display: flex; align-items: center; gap: var(--space-2);">
        <span class="material-symbols-outlined">support_agent</span>
        <span style="font-weight: 600;">NovaTrust Live Chat</span>
    </div>
    <button id="nova-chat-close" style="background: rgba(255,255,255,0.15); border: none; color: #ffffff; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0; transition: background 0.2s;">
        <span class="material-symbols-outlined" style="font-size: 18px;">close</span>
    </button>
</div>

    <div id="nova-chat-messages" style="flex-grow: 1; overflow-y: auto; padding: var(--space-4); display: flex; flex-direction: column; gap: var(--space-3); background: var(--slate-50);">
        <!-- Messages injected here via JS -->
        <div style="text-align: center; color: var(--slate-400); font-size: 0.85rem; margin-top: auto;">
            Starting conversation...
        </div>
    </div>

    <div style="padding: var(--space-3); border-top: 1px solid var(--slate-200); background: var(--color-surface); flex-shrink: 0;">
        <form id="nova-chat-form" style="display: flex; gap: var(--space-2);">
            <input type="text" id="nova-chat-input" placeholder="Type a message..." style="flex-grow: 1; padding: var(--space-2) var(--space-3); border: 1px solid var(--slate-200); border-radius: var(--radius-md); outline: none;">
            <button type="submit" class="btn btn-primary" style="padding: 0 var(--space-3); display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-outlined" style="font-size: 20px;">send</span>
            </button>
        </form>
    </div>
</div>

<style>
/* Mobile full-screen behavior per Design System 5.3 */
@media (max-width: 768px) {
    #nova-chat-widget {
        bottom: 0 !important;
        right: 0 !important;
        width: 100% !important;
        height: 100% !important;
        border-radius: 0 !important;
        border: none !important;
    }
}

.chat-bubble {
    max-width: 80%;
    padding: var(--space-2) var(--space-3);
    border-radius: var(--radius-md);
    font-size: 0.9rem;
    line-height: 1.4;
    word-break: break-word;
}
.chat-bubble.user {
    background: var(--color-primary);
    color: white;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
}
.chat-bubble.bot, .chat-bubble.admin {
    background: white;
    color: var(--color-ink);
    align-self: flex-start;
    border-bottom-left-radius: 4px;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--slate-100);
}
</style>
