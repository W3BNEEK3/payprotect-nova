(() => {
    const bellBtn = document.getElementById('notificationBellTrigger');
    const badge = document.getElementById('notificationBadge');
    const bellIcon = document.getElementById('notificationBellIcon');
    const dropdown = document.getElementById('notificationDropdown');
    const listContainer = document.getElementById('notificationList');
    const markAllBtn = document.getElementById('notificationMarkAll');
    
    if (!bellBtn || !dropdown) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const POLLING_INTERVAL = 45000; // 45 seconds

    function toggleDropdown() {
        const isExpanded = bellBtn.getAttribute('aria-expanded') === 'true';
        if (isExpanded) {
            dropdown.hidden = true;
            bellBtn.setAttribute('aria-expanded', 'false');
        } else {
            dropdown.hidden = false;
            bellBtn.setAttribute('aria-expanded', 'true');
        }
    }

    bellBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleDropdown();
    });

    document.addEventListener('click', (e) => {
        if (!bellBtn.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.hidden = true;
            bellBtn.setAttribute('aria-expanded', 'false');
        }
    });

    function updateUI(data) {
        // Update badge
        if (data.unread_count > 0) {
            badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
            badge.style.display = 'block';
            bellBtn.classList.add('has-unread');
        } else {
            badge.style.display = 'none';
            bellBtn.classList.remove('has-unread');
        }

        // Update list
        if (data.latest.length === 0) {
            listContainer.innerHTML = '<div style="padding: var(--space-6) var(--space-4); text-align: center; color: var(--slate-500); font-size: 14px;">No notifications</div>';
            return;
        }

        let html = '';
        data.latest.forEach(item => {
            const isRead = parseInt(item.is_read) === 1;
            const dateStr = new Date(item.created_at).toLocaleString();
            
            html += `
                <div class="notification-item ${isRead ? '' : 'unread'}" data-id="${item.id}" style="padding: var(--space-3) var(--space-4); border-bottom: 1px solid var(--slate-100); cursor: pointer; transition: background 0.2s; background: ${isRead ? 'transparent' : 'var(--slate-50)'};">
                    <div style="font-size: 14px; font-weight: ${isRead ? '500' : '600'}; color: var(--color-ink); margin-bottom: 4px;">
                        ${escapeHtml(item.title || 'Notification')}
                        ${!isRead ? '<span style="display:inline-block; width:6px; height:6px; background:var(--color-teal); border-radius:50%; margin-left:4px; vertical-align:middle;"></span>' : ''}
                    </div>
                    <div style="font-size: 13px; color: var(--slate-600); margin-bottom: 4px; line-height: 1.4;">${escapeHtml(item.message)}</div>
                    <div style="font-size: 11px; color: var(--slate-500);">${dateStr}</div>
                </div>
            `;
        });
        
        listContainer.innerHTML = html;

        // Attach click handlers to items
        listContainer.querySelectorAll('.notification-item').forEach(el => {
            el.addEventListener('click', (e) => {
                const id = el.getAttribute('data-id');
                if (el.classList.contains('unread')) {
                    markAsRead(id, el);
                }
            });
        });
    }

    async function fetchNotifications() {
        try {
            const res = await fetch('/api/notifications');
            if (!res.ok) return;
            const data = await res.json();
            updateUI(data);
        } catch (e) {
            console.error('Error fetching notifications:', e);
        }
    }

    async function markAsRead(id, element) {
        try {
            const formData = new URLSearchParams();
            formData.append('_csrf', csrfToken);
            
            const res = await fetch(`/api/notifications/${id}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData.toString()
            });
            
            if (res.ok) {
                // Optimistically update
                element.classList.remove('unread');
                element.style.background = 'transparent';
                element.querySelector('div').style.fontWeight = '500';
                const dot = element.querySelector('span');
                if (dot) dot.remove();
                
                // Fetch fresh counts
                fetchNotifications();
            }
        } catch (e) {
            console.error('Error marking as read:', e);
        }
    }

    if (markAllBtn) {
        markAllBtn.addEventListener('click', async () => {
            try {
                const formData = new URLSearchParams();
                formData.append('_csrf', csrfToken);
                
                const res = await fetch(`/api/notifications/mark-all-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData.toString()
                });
                
                if (res.ok) {
                    fetchNotifications();
                }
            } catch (e) {
                console.error('Error marking all as read:', e);
            }
        });
    }

    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return (unsafe || '').toString()
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    // Initial fetch
    fetchNotifications();

    // Poll every 45 seconds
    setInterval(fetchNotifications, POLLING_INTERVAL);

})();
