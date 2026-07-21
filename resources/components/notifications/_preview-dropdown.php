<div id="notificationDropdown" class="notification-dropdown" hidden>
    <div style="padding: var(--space-4); border-bottom: 1px solid var(--slate-100); display: flex; justify-content: space-between; align-items: center;">
        <h4 style="margin: 0; font: 600 16px/1.2 var(--font-display); color: var(--color-ink);">Notifications</h4>
        <button type="button" id="notificationMarkAll" style="background: none; border: none; color: var(--color-teal); font-size: 13px; font-weight: 500; cursor: pointer; padding: 0;">Mark all read</button>
    </div>
    
    <div id="notificationList" style="max-height: 360px; overflow-y: auto;">
        <!-- JS populates items here -->
        <div style="padding: var(--space-6) var(--space-4); text-align: center; color: var(--slate-500); font-size: 14px;">
            Loading...
        </div>
    </div>
    
    <div style="padding: var(--space-3) var(--space-4); border-top: 1px solid var(--slate-100); text-align: center;">
        <a href="/notifications" style="color: var(--color-teal); font-size: 14px; font-weight: 500; text-decoration: none;">View all notifications</a>
    </div>
</div>
