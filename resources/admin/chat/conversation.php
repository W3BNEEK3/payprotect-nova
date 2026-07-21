<?php ob_start(); 
/** @var array $user */
/** @var array $conversation */
/** @var array $messages */
?>
<style>
    .chat-conversation-grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: var(--space-6);
        padding-bottom: var(--space-8);
        height: calc(100vh - 100px);
    }
    .chat-view-container {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: var(--color-surface);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--slate-100);
    }
    @media (max-width: 768px) {
        .chat-conversation-grid {
            grid-template-columns: 1fr;
            height: auto;
        }
        .chat-view-container {
            height: 600px;
        }
    }
</style>
<div class="chat-conversation-page chat-conversation-grid">

    <!-- User Context Sidebar -->
    <div style="background: var(--color-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--slate-100); padding: var(--space-6); overflow-y: auto;">
        <div style="margin-bottom: var(--space-4);">
            <a href="/admin/chat/queue" class="btn btn-ghost" style="display: inline-flex; align-items: center; margin-left: -8px;">
                <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 4px;">arrow_back</span>
                Back to Queue
            </a>
        </div>
        
        <h3 style="font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); color: var(--color-ink); margin: 0 0 var(--space-4);">User Context</h3>
        
        <div style="display: flex; flex-direction: column; gap: var(--space-4);">
            <div>
                <div style="font-size: 0.8rem; color: var(--slate-500); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Name</div>
                <div style="color: var(--color-ink); font-weight: 500;"><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></div>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--slate-500); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Email</div>
                <div style="color: var(--color-ink);"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--slate-500); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Balance</div>
                <div style="color: var(--color-ink); font-weight: 600; font-family: var(--font-mono);"><?= htmlspecialchars($user['currency'] ?? 'USD') ?> <?= number_format($user['balance'] ?? 0, 2) ?></div>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--slate-500); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Status</div>
                <div style="color: var(--color-ink);"><?= htmlspecialchars(ucfirst($user['account_status'] ?? 'active')) ?></div>
            </div>
        </div>

        <?php if ($conversation['status'] !== 'closed'): ?>
            <div style="margin-top: var(--space-8);">
                <form action="/admin/chat/conversation/<?= $conversation['id'] ?>/close" method="POST">
                    <?= \App\Middlewares\CsrfMiddleware::field() ?>
                    <button type="submit" class="btn btn-secondary" style="width: 100%; color: var(--color-danger); border-color: var(--color-danger);">Close Conversation</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Chat View -->
    <div class="chat-view-container">
        <div style="padding: var(--space-4) var(--space-6); border-bottom: 1px solid var(--slate-100); background: var(--slate-50);">
            <h2 style="font: 600 var(--type-heading-md-size)/var(--type-heading-md-lh) var(--font-display); color: var(--color-ink); margin: 0;">Conversation #<?= $conversation['id'] ?></h2>
        </div>
        
        <div id="admin-chat-messages" style="flex-grow: 1; overflow-y: auto; padding: var(--space-6); display: flex; flex-direction: column; gap: var(--space-4); background: white;">
            <?php foreach ($messages as $msg): ?>
                <div class="chat-bubble <?= htmlspecialchars($msg['sender_type']) ?>">
                    <div style="font-size: 0.75rem; opacity: 0.7; margin-bottom: 2px;">
                        <?= $msg['sender_type'] === 'user' ? 'User' : ($msg['sender_type'] === 'admin' ? 'Agent' : 'Bot') ?>
                        <span style="float: right;"><?= date('H:i', strtotime($msg['created_at'])) ?></span>
                    </div>
                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($conversation['status'] !== 'closed'): ?>
        <div style="padding: var(--space-4) var(--space-6); border-top: 1px solid var(--slate-100); background: var(--slate-50);">
           
            <form id="admin-chat-form" style="display: flex; gap: var(--space-3); align-items: flex-end;">
                <?= \App\Middlewares\CsrfMiddleware::field() ?>
                <textarea id="admin-chat-input" name="message" placeholder="Type a message to the user..." rows="2" style="flex-grow: 1; padding: var(--space-3); border: 1px solid var(--slate-200); border-radius: var(--radius-md); outline: none; resize: none; font-family: inherit; font-size: 0.95rem; line-height: 1.5;" required></textarea>
                <button type="submit" class="btn btn-primary" style="padding: 0 var(--space-4); height: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="margin-right: 8px;">send</span> Send
                </button>
            </form>
        </div>
        <?php else: ?>
        <div style="padding: var(--space-4) var(--space-6); border-top: 1px solid var(--slate-100); background: var(--slate-50); text-align: center; color: var(--slate-500);">
            This conversation is closed.
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.chat-bubble {
    max-width: 70%;
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius-md);
    font-size: 0.95rem;
    line-height: 1.5;
    word-break: break-word;
}
.chat-bubble.admin {
    background: var(--color-teal);
    color: white;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
}
.chat-bubble.user {
    background: var(--slate-100);
    color: var(--color-ink);
    align-self: flex-start;
    border-bottom-left-radius: 4px;
}
.chat-bubble.bot {
    background: var(--teal-50);
    color: var(--teal-900);
    align-self: center;
    border-radius: var(--radius-md);
    font-size: 0.85rem;
    max-width: 90%;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('admin-chat-messages');
    container.scrollTop = container.scrollHeight;
    
    let lastMessageId = <?= !empty($messages) ? end($messages)['id'] : 0 ?>;
    const conversationId = <?= $conversation['id'] ?>;
    
    // Audio context for sound notification
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    
    function playNotificationSound() {
        try {
            if (audioContext.state === 'suspended') {
                audioContext.resume();
            }
            const osc = audioContext.createOscillator();
            const gain = audioContext.createGain();
            osc.connect(gain);
            gain.connect(audioContext.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, audioContext.currentTime);
            osc.frequency.exponentialRampToValueAtTime(1760, audioContext.currentTime + 0.1);
            gain.gain.setValueAtTime(0.1, audioContext.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
            osc.start();
            osc.stop(audioContext.currentTime + 0.1);
        } catch (e) {
            console.error('Audio play failed', e);
        }
    }

    if (window.PUSHER_KEY) {
        Pusher.logToConsole = false;
        
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        const pusher = new Pusher(window.PUSHER_KEY, {
            cluster: window.PUSHER_CLUSTER,
            channelAuthorization: {
                endpoint: '/api/chat/admin/auth',
                transport: 'ajax',
                headers: {
                    'X-CSRF-Token': csrfToken
                }
            }
        });

        const channel = pusher.subscribe(`private-chat-${conversationId}`);
        
        channel.bind('new-message', function(msg) {
            if (msg.id > lastMessageId) {
                // remove optimistic loader
                const loaders = container.querySelectorAll('.chat-optimistic-loader');
                loaders.forEach(l => l.remove());
                
                const div = document.createElement('div');
                div.className = `chat-bubble ${msg.sender_type}`;
                
                const meta = document.createElement('div');
                meta.style.cssText = "font-size: 0.75rem; opacity: 0.7; margin-bottom: 2px;";
                meta.innerHTML = `${msg.sender_type === 'user' ? 'User' : (msg.sender_type === 'admin' ? 'Agent' : 'Bot')} <span style="float: right;">Now</span>`;
                
                const text = document.createElement('div');
                text.textContent = msg.message;
                
                div.appendChild(meta);
                div.appendChild(text);
                container.appendChild(div);
                
                lastMessageId = msg.id;
                container.scrollTop = container.scrollHeight;
                
                if (msg.sender_type === 'user') {
                    playNotificationSound();
                }
            }
        });

        channel.bind('status-change', function(data) {
            if (data.status === 'closed') {
                const div = document.createElement('div');
                div.className = `chat-bubble bot`;
                div.style.cssText = "font-style: italic; opacity: 0.8;";
                div.textContent = "This conversation was closed.";
                container.appendChild(div);
                container.scrollTop = container.scrollHeight;
            }
        });
    }

    const form = document.getElementById('admin-chat-form');
    const adminInput = document.getElementById('admin-chat-input');

    // Enter submits, Shift+Enter inserts newline
    if (adminInput) {
        adminInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (form) form.dispatchEvent(new Event('submit', { cancelable: true }));
            }
        });
    }

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = document.getElementById('admin-chat-input');

            const message = input.value.trim();
            if (!message) return;
            
            // Capture FormData BEFORE clearing the input
            const requestData = new FormData(form);
            
            input.value = '';
            input.disabled = true;

            const loaderHtml = `
                <div class="chat-optimistic-loader" style="align-self: flex-end; background: var(--color-teal); color: white; padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); border-bottom-right-radius: 4px; margin-left: auto;">
                    <div class="chat-typing-indicator">
                        <div class="chat-typing-dot"></div>
                        <div class="chat-typing-dot"></div>
                        <div class="chat-typing-dot"></div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', loaderHtml);
            container.scrollTop = container.scrollHeight;

            try {
                const response = await fetch(`/api/chat/admin/messages/${conversationId}`, {
                    method: 'POST',
                    body: requestData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                
                if (response.redirected) {
                    console.error('Request was redirected. Possible auth or CSRF failure.');
                    alert(`Error: Redirected to ${response.url}`);
                    input.value = message;
                } else if (!response.ok) {
                    const body = await response.text();
                    console.error('Send failed', response.status, body);
                    alert(`Error: ${response.status}`);
                    input.value = message; // restore on failure
                }
            } catch (error) {
                console.error('Send error', error);
                alert('Send error');
                input.value = message;
            } finally {
                const loaders = container.querySelectorAll('.chat-optimistic-loader');
                loaders.forEach(l => l.remove());
                
                input.disabled = false;
                input.focus();
            }
        });
    }});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
