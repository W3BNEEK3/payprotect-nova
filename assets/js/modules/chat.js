class NovaChat {
    constructor() {
        this.isOpen = false;
        this.conversationId = null;
        this.lastMessageId = 0;
        this.widgetLoaded = false;
        this.initialized = false; // tracks if placeholder has been cleared
        this.pusher = null;
        this.currentChannel = null;
        this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }

    async playNotificationSound() {
        if (this.audioContext.state === 'suspended') {
            await this.audioContext.resume();
        }
        const osc = this.audioContext.createOscillator();
        const gain = this.audioContext.createGain();
        osc.connect(gain);
        gain.connect(this.audioContext.destination);
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, this.audioContext.currentTime);
        osc.frequency.exponentialRampToValueAtTime(440, this.audioContext.currentTime + 0.1);
        gain.gain.setValueAtTime(0.1, this.audioContext.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.1);
        osc.start();
        osc.stop(this.audioContext.currentTime + 0.1);
    }

    init() {
        const startBtn = document.getElementById('open-live-chat');
        if (startBtn) {
            startBtn.addEventListener('click', () => this.open());
        }
    }

    async loadWidget() {
        if (this.widgetLoaded) return;
        
        try {
            const response = await fetch('/support/chat/widget');
            const html = await response.text();
            
            const container = document.createElement('div');
            container.innerHTML = html;
            document.body.appendChild(container);
            
            this.bindWidgetEvents();
            this.widgetLoaded = true;
        } catch (error) {
            console.error('Failed to load chat widget', error);
        }
    }

    bindWidgetEvents() {
        document.getElementById('nova-chat-close').addEventListener('click', () => this.close());
        
        const form = document.getElementById('nova-chat-form');
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.sendMessage();
        });
    }

    async open() {
        await this.loadWidget();
        
        document.getElementById('nova-chat-widget').style.display = 'flex';
        this.isOpen = true;
        // NOTE: do NOT set body overflow:hidden — it clips position:fixed widgets in Chrome
        
        this.checkStatus();
        this.checkStatus();
    }

    close() {
        document.getElementById('nova-chat-widget').style.display = 'none';
        this.isOpen = false;
        this.isOpen = false;
    }

    async checkStatus() {
        try {
            const response = await fetch('/api/chat/status');
            const data = await response.json();
            
            if (data.status === 'success' && data.conversation) {
                this.conversationId = data.conversation.id;
                this.lastConversationStatus = data.conversation.status;
                this.initPusher();
                this.subscribeToConversation();
                this.fetchMessages();
            } else {
                document.getElementById('nova-chat-messages').innerHTML = `
                    <div style="text-align: center; color: var(--slate-400); font-size: 0.85rem; margin-top: auto;">
                        Send a message to start a conversation.
                    </div>
                `;
            }
        } catch (error) {
            console.error('Failed to check chat status', error);
        }
    }

    async fetchMessages() {
        if (!this.conversationId) return;
        
        try {
            const response = await fetch(`/api/chat/${this.conversationId}/messages?after=${this.lastMessageId}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                if (data.messages && data.messages.length > 0) {
                    this.renderMessages(data.messages);
                }
                
                if (data.conversation_status === 'closed' && this.lastConversationStatus !== 'closed') {
                    this.renderSystemMessage('This conversation was closed by an agent. Send a new message to start a new chat.');
                }
                
                this.lastConversationStatus = data.conversation_status;
            }
        } catch (error) {
            console.error('Failed to fetch messages', error);
        }
    }

    renderSystemMessage(text) {
        const container = document.getElementById('nova-chat-messages');
        const div = document.createElement('div');
        div.style.cssText = 'text-align: center; color: var(--slate-400); font-size: 0.85rem; margin: 12px 0; font-style: italic;';
        div.textContent = text;
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    renderMessages(messages) {
        const container = document.getElementById('nova-chat-messages');
        
        // Remove "Starting conversation..." message and any optimistic loaders
        const initialMsg = container.querySelector('div[style*="Starting conversation"]');
        if (initialMsg) initialMsg.remove();
        
        const loaders = container.querySelectorAll('.chat-optimistic-loader');
        loaders.forEach(l => l.remove());

        // Only clear placeholder text once, never wipe real messages
        if (!this.initialized) {
            container.innerHTML = '';
            this.initialized = true;
        }

        messages.forEach(msg => {
            const div = document.createElement('div');
            const isUser = msg.sender_type === 'user';
            const isBot = msg.sender_type === 'bot';
            const isAdmin = msg.sender_type === 'admin';
            
            // Inline styles — no dependency on CSS class loading from fetched HTML
            div.style.cssText = [
                'max-width: 80%',
                'padding: 8px 12px',
                'border-radius: 12px',
                'font-size: 0.9rem',
                'line-height: 1.4',
                'word-break: break-word',
                isUser
                    ? 'background: var(--color-primary, #0d9488); color: white; align-self: flex-end; border-bottom-right-radius: 4px;'
                    : isAdmin
                        ? 'background: var(--teal-50, #f0fdfa); color: var(--color-ink, #1e293b); align-self: flex-start; border-bottom-left-radius: 4px; border: 1px solid var(--teal-100, #ccfbf1);'
                        : 'background: white; color: var(--color-ink, #1e293b); align-self: flex-start; border-bottom-left-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.06); border: 1px solid #f1f5f9;'
            ].join('; ');
            
            const safeText = msg.message.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
            
            if (isBot) {
                div.innerHTML = `<div style="display: flex; gap: 8px; align-items: flex-start;">
                    <span class="material-symbols-outlined" style="font-size: 18px; color: var(--slate-500); margin-top: 2px;">smart_toy</span>
                    <div style="flex-grow: 1;">${safeText}</div>
                </div>`;
            } else if (isAdmin) {
                div.innerHTML = `<div style="display: flex; gap: 8px; align-items: flex-start;">
                    <span class="material-symbols-outlined" style="font-size: 18px; color: var(--color-primary, #0d9488); margin-top: 2px;">support_agent</span>
                    <div style="flex-grow: 1;">${safeText}</div>
                </div>`;
            } else {
                div.innerHTML = safeText;
            }
            
            container.appendChild(div);
            
            if (msg.id > this.lastMessageId) {
                this.lastMessageId = msg.id;
            }
        });
        
        container.scrollTop = container.scrollHeight;
    }

    async sendMessage() {
        const input = document.getElementById('nova-chat-input');
        const message = input.value.trim();
        
        if (!message) return;
        
        input.value = '';
        input.disabled = true;

        const container = document.getElementById('nova-chat-messages');
        const loaderHtml = `
            <div class="chat-optimistic-loader" style="align-self: flex-end; background: var(--color-primary, #0d9488); color: white; padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); border-bottom-right-radius: 4px; margin-left: auto;">
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
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
            
            const formData = new URLSearchParams();
            formData.append('message', message);
            formData.append('_csrf', csrfToken);
            
            const response = await fetch('/api/chat/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            });
            
            const data = await response.json();
            if (data.status === 'success') {
                this.conversationId = data.conversation_id;
                await this.fetchMessages(); // fetch user msg + bot reply
            } else {
                // Restore message on failure
                input.value = message;
                console.error('Send failed', data);
            }
        } catch (error) {
            console.error('Failed to send message', error);
            input.value = message;
        } finally {
            const loaders = document.querySelectorAll('.chat-optimistic-loader');
            loaders.forEach(l => l.remove());
            
            input.disabled = false;
            input.focus();
        }
    }

    initPusher() {
        if (!window.PUSHER_KEY || this.pusherInitialized) return;
        
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        // Add CSRF token for private channel auth
        Pusher.logToConsole = false;
        this.pusher = new Pusher(window.PUSHER_KEY, {
            cluster: window.PUSHER_CLUSTER,
            channelAuthorization: {
                endpoint: '/api/chat/auth',
                transport: 'ajax',
                headers: {
                    'X-CSRF-Token': csrfToken
                }
            }
        });
        
        this.pusherInitialized = true;
    }

    subscribeToConversation() {
        if (!this.pusher || !this.conversationId) return;
        
        const channelName = `private-chat-${this.conversationId}`;
        if (this.currentChannel === channelName) return;

        if (this.currentChannel) {
            this.pusher.unsubscribe(this.currentChannel);
        }

        this.currentChannel = channelName;
        const channel = this.pusher.subscribe(channelName);
        
        channel.bind('new-message', (data) => {
            // Check if we already have it to avoid dupes
            if (data.id > this.lastMessageId) {
                this.renderMessages([data]);
                if (data.sender_type !== 'user' && this.isOpen) {
                    this.playNotificationSound();
                }
            }
        });
        
        channel.bind('status-change', (data) => {
            if (data.status === 'closed' && this.lastConversationStatus !== 'closed') {
                this.renderSystemMessage('This conversation was closed by an agent. Send a new message to start a new chat.');
            }
            this.lastConversationStatus = data.status;
        });
    }

    playNotificationSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gainNode = ctx.createGain();
            
            osc.connect(gainNode);
            gainNode.connect(ctx.destination);
            
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(1760, ctx.currentTime + 0.1);
            
            gainNode.gain.setValueAtTime(0, ctx.currentTime);
            gainNode.gain.linearRampToValueAtTime(0.1, ctx.currentTime + 0.05);
            gainNode.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
            
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.3);
        } catch (e) {
            console.error('Audio play failed', e);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.novaChat = new NovaChat();
    window.novaChat.init();
});
